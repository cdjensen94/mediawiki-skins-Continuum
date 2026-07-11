/** @module search */

const
	Vue = require( 'vue' ),
	{
		App,
		restSearchClient,
		urlGenerator
	} = require( /** @type {string} */ ( 'mediawiki.skinning.typeaheadSearch' ) ),
	createWikibaseCompatSearchClient = require( './wikibaseCompatSearchClient.js' );

const searchConfig = require( './searchConfig.json' );
const inNamespace = searchConfig.ContentNamespaces.includes( mw.config.get( 'wgNamespaceNumber' ) );
// apiUrl defaults to /rest.php if not set
const searchApiUrl = searchConfig.ContinuumTypeahead.apiUrl || mw.config.get( 'wgScriptPath' ) + '/rest.php';
const recommendationApiUrl = inNamespace ? searchConfig.ContinuumTypeahead.recommendationApiUrl : '';
const searchOptions = searchConfig.ContinuumTypeahead.options;
const useWikibaseSearchCompatibility = !!searchConfig.useWikibaseSearchCompatibility;
// The param config must be defined for empty search recommendations to be enabled.
const showEmptySearchRecommendations = inNamespace && recommendationApiUrl;

/**
 * Best-effort detection for Wikibase Item namespace results.
 *
 * @param {Object} result
 * @return {boolean}
 */
function isItemResult( result ) {
	if ( !result || typeof result !== 'object' ) {
		return false;
	}

	const title = typeof result.title === 'string' ? result.title : '';
	const pageTitle = typeof result.page_title === 'string' ? result.page_title : '';
	const key = typeof result.key === 'string' ? result.key : '';
	const namespace = typeof result.namespace === 'number' ? result.namespace : result.ns;

	return (
		title.startsWith( 'Item:' ) ||
		pageTitle.startsWith( 'Item:' ) ||
		key.startsWith( 'Item:' ) ||
		namespace === 120
	);
}

/**
 * Sort normal pages before Wikibase Items.
 *
 * Keeps relative order otherwise.
 *
 * @param {Array<Object>} results
 * @return {Array<Object>}
 */
function sortPagesBeforeItems( results ) {
	if ( !Array.isArray( results ) ) {
		return results;
	}

	return [ ...results ].sort( ( a, b ) => {
		const aIsItem = isItemResult( a );
		const bIsItem = isItemResult( b );

		if ( aIsItem === bIsItem ) {
			return 0;
		}

		return aIsItem ? 1 : -1;
	} );
}

/**
 * Optional hard filter for Item namespace results.
 *
 * @type {boolean}
 */
const HIDE_ITEM_RESULTS = false;

/**
 * @param {Array<Object>} results
 * @return {Array<Object>}
 */
function processResults( results ) {
	if ( !Array.isArray( results ) ) {
		return results;
	}

	let processed = results;

	if ( HIDE_ITEM_RESULTS ) {
		processed = processed.filter( ( result ) => !isItemResult( result ) );
	}

	return sortPagesBeforeItems( processed );
}

/**
 * @param {Object} search
 * @return {Object}
 */
function wrapSearchFetch( search ) {
	return {
		abort: search.abort,
		fetch: search.fetch.then( ( data ) => Object.assign( {}, data, {
			results: processResults( data.results )
		} ) )
	};
}

/**
 * @param {Array<Object>} resultSets
 * @return {Array<Object>}
 */
function mergeSearchResults( resultSets ) {
	const merged = [];
	const seen = new Set();

	resultSets.forEach( ( resultSet ) => {
		const results = Array.isArray( resultSet.results ) ? resultSet.results : [];

		results.forEach( ( result ) => {
			const identity =
				( typeof result.url === 'string' && result.url ) ||
				( typeof result.key === 'string' && result.key ) ||
				( typeof result.value === 'string' && result.value ) ||
				( typeof result.title === 'string' && result.title ) ||
				( typeof result.label === 'string' && result.label );

			if ( identity && seen.has( identity ) ) {
				return;
			}
			if ( identity ) {
				seen.add( identity );
			}

			merged.push( result );
		} );
	} );

	return merged;
}

/**
 * @param {Array<Object|null|undefined>} searches
 * @return {Object}
 */
function combineSearchFetches( searches ) {
	const activeSearches = searches.filter( Boolean );

	return {
		abort: () => {
			activeSearches.forEach( ( search ) => {
				if ( typeof search.abort === 'function' ) {
					search.abort();
				}
			} );
		},
		fetch: Promise.allSettled(
			activeSearches.map( ( search ) => search.fetch )
		).then( ( settledResults ) => {
			const fulfilledResults = settledResults
				.filter( ( result ) => result.status === 'fulfilled' )
				.map( ( result ) => result.value );

			if ( !fulfilledResults.length ) {
				return Promise.reject( new Error( 'All search providers failed.' ) );
			}

			return {
				query: fulfilledResults[ 0 ].query || '',
				results: processResults( mergeSearchResults( fulfilledResults ) )
			};
		} )
	};
}

/**
 * @param {Object} baseClient
 * @param {Object|null} wikibaseClient
 * @return {Object}
 */
function createContinuumSearchClient( baseClient, wikibaseClient = null ) {
	return {
		fetchRecommendationByTitle: typeof baseClient.fetchRecommendationByTitle === 'function' ?
			( currentTitle, showDescription ) => wrapSearchFetch(
				baseClient.fetchRecommendationByTitle( currentTitle, showDescription )
			) :
			undefined,
		fetchByTitle: wikibaseClient ?
			( query, limit, showDescription ) => combineSearchFetches( [
				baseClient.fetchByTitle( query, limit, showDescription ),
				wikibaseClient.fetchByTitle( query, limit, showDescription )
			] ) :
			( query, limit, showDescription ) => wrapSearchFetch(
				baseClient.fetchByTitle( query, limit, showDescription )
			),
		loadMore: !wikibaseClient && typeof baseClient.loadMore === 'function' ?
			( query, offset, limit, showDescription ) => wrapSearchFetch(
				baseClient.loadMore( query, offset, limit, showDescription )
			) :
			undefined
	};
}

/**
 * @param {Element} searchBox
 * @param {Object} [restClient]
 * @param {Object} [urlGeneratorInstance]
 * @return {void}
 */
function initApp( searchBox, restClient, urlGeneratorInstance ) {
	// The config variables enable customization of the URL generator and search client
	// by Wikidata. Note: These must be defined by Wikidata in the page HTML and are not
	// read from LocalSettings.php
	const urlGeneratorConfig = mw.config.get(
		'wgContinuumSearchUrlGenerator'
	);
	const searchClientConfig = mw.config.get(
		'wgContinuumSearchClient'
	);

	if ( urlGeneratorConfig ) {
		mw.log.warn( `Use of mw.config.get( "wgContinuumSearchUrlGenerator") is deprecated.
Use SkinPageReadyConfig hook to replace the search module (T395641).` );
	}
	if ( searchClientConfig ) {
		mw.log.warn( `Use of mw.config.get( "wgContinuumSearchClient") is deprecated.
Use SkinPageReadyConfig hook to replace the search module (T395641).` );
	}

	urlGeneratorInstance = urlGeneratorInstance || urlGeneratorConfig ||
		urlGenerator( mw.config.get( 'wgScript' ) );

	const baseClient = restClient || searchClientConfig ||
		restSearchClient(
			searchApiUrl,
			urlGeneratorInstance,
			recommendationApiUrl
		);
	const wikibaseClient = useWikibaseSearchCompatibility ?
		createWikibaseCompatSearchClient() :
		null;

	restClient = createContinuumSearchClient( baseClient, wikibaseClient );

	const searchForm = searchBox.querySelector( '.cdx-search-input' ),
		titleInput = /** @type {HTMLInputElement|null} */ (
			searchBox.querySelector( 'input[name=title]' )
		),
		search = /** @type {HTMLInputElement|null} */ ( searchBox.querySelector( 'input[name=search]' ) ),
		searchPageTitle = titleInput && titleInput.value,
		searchContainer = searchBox.querySelector( '.continuum-typeahead-search-container' );

	if ( !searchForm || !search || !titleInput ) {
		throw new Error( 'Attempted to create Vue search element from an incompatible element.' );
	}

	// @ts-ignore MediaWiki-specific function
	Vue.createMwApp(
		App, Object.assign( {
			prefixClass: 'continuum-',
			id: searchForm.id,
			autocapitalizeValue: search.getAttribute( 'autocapitalize' ),
			autofocusInput: search === document.activeElement,
			action: searchForm.getAttribute( 'action' ),
			searchAccessKey: search.getAttribute( 'accessKey' ),
			searchPageTitle,
			restClient,
			urlGenerator: urlGeneratorInstance,
			searchTitle: search.getAttribute( 'title' ),
			searchPlaceholder: search.getAttribute( 'placeholder' ),
			searchQuery: search.value,
			autoExpandWidth: searchBox ? searchBox.classList.contains( 'continuum-search-box-auto-expand-width' ) : false,
			showEmptySearchRecommendations
		// Pass additional config from server.
		}, searchOptions )
	)
		.mount( searchContainer );
}

/**
 * @param {Document} document
 * @param {Object} [restClient]
 * @param {Object} [urlGeneratorInstance]
 * @return {void}
 */
function main( document, restClient, urlGeneratorInstance ) {
	document.querySelectorAll( '.continuum-search-box' )
		.forEach( ( node ) => {
			initApp( node, restClient, urlGeneratorInstance );
		} );
}

/**
 * @ignore
 * @param {Object} [restClient] used by Wikidata to configure the search API
 * @param {Object} [urlGeneratorInstance] used by Wikidata to configure the search API
 */
function init( restClient, urlGeneratorInstance ) {
	main( document, restClient, urlGeneratorInstance );
}

module.exports = {
	init
};
