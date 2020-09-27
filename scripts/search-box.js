let ajaxSearchBar = null;

function searchDatabase() {
	clearAjax()
	let query = $('#search-field')[0].value
	if (!query.length) {
		$('.search-result').remove()
		$('#search-results').hide()
		return
	}

	let url = '/api/searchDatabase.php'
	let type = $('#search-type')[0].value
	let data = {query, type}
	ajaxSearchBar = $.get(url, data, searchDatabaseSuccess)
}

function clearAjax() {
	if (!ajaxSearchBar) return

	ajaxSearchBar.abort()
	ajaxSearchBar = null
}

function searchDatabaseSuccess(res) {
	$('.search-result').remove()
	if (res.results.length == 0)
		return $('#search-results').hide()

	$('#search-results').show()
	for (let r of res.results) {
		let field1;
		let class2;
		if (res.type === 'vid' || res.detail === 1) {
			field1 = r.id;
			field2 = ` ${r.name}`;
			class2 = 'x-pic';
		} else {
			field1 = `\n<img src='/media/stars/${r.id}.jpg'>`;
			field2 = `\n<span>${r.name}</span>`;
			class2 = 'w-pic';
		}
		$('#search-results').append(`<a class='search-result ${class2}' href='/${res.type}/${r.id}'>${field1}${field2}</a>`);
	}
}