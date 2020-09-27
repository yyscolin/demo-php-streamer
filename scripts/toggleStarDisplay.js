function toggleStarDisplay(button) {
    let http = new XMLHttpRequest();
    let id = button.value;
    let value = button.innerHTML === 'show' ? 1 : 0;
    http.open('POST', '/api/toggleStarDisplay.php', true);
    http.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    http.send(`id=${id}&value=${value}`);
    http.onload = function() {
        if (http.status === 200)
            button.innerHTML = value === 1 ? 'hide' : 'show';
    }
}