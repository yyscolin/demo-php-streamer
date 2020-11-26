let searchAjax = null

function searchDatabase() {
	clearSearchAjax()
	let query = $('#search-field')[0].value
	if (!query.length) {
		$('.search-result').remove()
		$('#search-results').hide()
		return
	}

	let url = '/api/searchDatabase.php'
	let type = $('#search-type')[0].value
	let data = {query, type}
	searchAjax = $.get(url, data, searchDatabaseSuccess)
}

function clearSearchAjax() {
	if (searchAjax) {
		searchAjax.abort()
		searchAjax = null
	}
}

function searchDatabaseSuccess(res) {
	$('.search-result').remove()
	if (res.results.length == 0)
		return $('#search-results').hide()

	$('#search-results').show()
	for (let r of res.results) {
		if (res.type === 'vid') {
			var innerHTML = `${r.id} ${r.name}`
			var className = 'x-pic'
		} else {
			var innerHTML = `<img src='/media/stars/${r.id}.jpg'><span>${r.name}</span>`
			var className = 'w-pic'
		}
		$('#search-results').append(`<a class='search-result ${className}' href='/${res.type}/${r.id}'>${innerHTML}</a>`)
	}
}