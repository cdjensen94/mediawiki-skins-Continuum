/**
 * Creates a Continuum-local Wikibase Item search client.
 *
 * This mirrors the Wikibase typeahead Item search behavior but keeps the integration inside
 * Continuum so Wikibase does not need to replace Continuum's search module.
 *
 * @return {Object}
 */
function createWikibaseCompatSearchClient() {
	/**
	 * @param {Object} match
	 * @param {string|undefined} labelText
	 * @return {string}
	 */
	function getMatchText( match = {}, labelText ) {
		const type = match.type;
		const text = match.text;

		if ( !type || !text ) {
			return '';
		}
		if ( [ 'alias', 'entityId' ].includes( type ) ) {
			return mw.msg( 'parentheses', text );
		}
		if ( type === 'label' && text !== labelText ) {
			return mw.msg( 'parentheses', text );
		}

		return '';
	}

	/**
	 * @param {string} query
	 * @param {number} batchSize
	 * @param {number|null} offset
	 * @return {Object}
	 */
	function fetchResults( query, batchSize, offset = null ) {
		const api = new mw.Api();
		const data = {
			action: 'wbsearchentities',
			search: query,
			limit: batchSize,
			format: 'json',
			errorformat: 'plaintext',
			language: mw.config.get( 'wgUserLanguage' ),
			uselang: mw.config.get( 'wgUserLanguage' ),
			type: 'item'
		};

		if ( offset ) {
			data.continue = offset;
		}

		return {
			fetch: api.get( data ).then( ( res ) => ( {
				query,
				results: ( res.search || [] ).map( ( {
					id,
					label,
					url,
					match,
					description,
					display = {}
				} ) => ( {
					value: id,
					label,
					title: label,
					key: id,
					namespace: 120,
					match: getMatchText( match, display.label && display.label.value ),
					description,
					url,
					language: {
						label: display.label ? display.label.language : undefined,
						match: match && [ 'alias', 'label' ].includes( match.type ) ? match.language : undefined,
						description: display.description ? display.description.language : undefined
					}
				} ) )
			} ) ),
			abort: () => {
				api.abort();
			}
		};
	}

	return {
		fetchByTitle: ( query, limit = 10 ) => fetchResults( query, limit ),
		loadMore: ( query, offset, limit = 10 ) => fetchResults( query, limit, offset )
	};
}

module.exports = createWikibaseCompatSearchClient;
