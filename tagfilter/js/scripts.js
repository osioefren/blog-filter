function filterPosts(tag, event) {
    var posts = document.getElementsByClassName('post');
    var buttons = document.getElementsByClassName('filter-button');

    // Reset all buttons
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove('active');
    }

    // Show or hide posts based on the selected tag
    for (var i = 0; i < posts.length; i++) {
        var tags = posts[i].getAttribute('data-tags');

        if (tag === 'all' || tags.includes(tag)) {
            posts[i].style.display = 'flex'; // Ensure the post is displayed as flex
        } else {
            posts[i].style.display = 'none'; // Hide posts that do not match
        }
    }

    // Add active class to the clicked button
    event.target.classList.add('active');
}
