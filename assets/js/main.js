// Real-time poll results update
function updatePollResults(pollId) {
    fetch(`api/get_poll_results.php?id=${pollId}`)
        .then(response => response.json())
        .then(data => {
            const resultsContainer = document.querySelector('.poll-results');
            if (resultsContainer) {
                let html = '<h4>Results</h4>';
                data.options.forEach(option => {
                    const percentage = data.total_votes > 0 ? (option.vote_count / data.total_votes) * 100 : 0;
                    html += `
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>${option.option_text}</span>
                                <span>${option.vote_count} votes (${Math.round(percentage)}%)</span>
                            </div>
                            <div class="result-bar">
                                <div class="result-fill" style="width: ${percentage}%"></div>
                            </div>
                        </div>
                    `;
                });
                html += `<p class="text-muted">Total votes: ${data.total_votes}</p>`;
                resultsContainer.innerHTML = html;
            }
        })
        .catch(error => console.error('Error updating results:', error));
}

// Real-time comments update
function updateComments(pollId) {
    fetch(`api/get_comments.php?id=${pollId}`)
        .then(response => response.json())
        .then(data => {
            const commentsContainer = document.querySelector('.comments-list');
            if (commentsContainer) {
                let html = '';
                data.comments.forEach(comment => {
                    html += `
                        <div class="comment">
                            <div class="d-flex justify-content-between">
                                <strong>${comment.user_name}</strong>
                                <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
                            </div>
                            <p class="mb-0">${comment.comment_text}</p>
                        </div>
                    `;
                });
                commentsContainer.innerHTML = html;
            }
        })
        .catch(error => console.error('Error updating comments:', error));
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const passwordInput = document.getElementById('new_password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            updatePasswordStrengthIndicator(strength);
        });
    }

    // Real-time updates for active polls
    const pollId = new URLSearchParams(window.location.search).get('id');
    if (pollId) {
        // Update results every 30 seconds
        setInterval(() => updatePollResults(pollId), 30000);
        // Update comments every 10 seconds
        setInterval(() => updateComments(pollId), 10000);
    }
});

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    return strength;
}

// Update password strength indicator
function updatePasswordStrengthIndicator(strength) {
    const indicator = document.querySelector('.password-strength');
    if (indicator) {
        let text = '';
        let color = '';
        switch (strength) {
            case 0:
            case 1:
                text = 'Very Weak';
                color = 'danger';
                break;
            case 2:
                text = 'Weak';
                color = 'warning';
                break;
            case 3:
                text = 'Medium';
                color = 'info';
                break;
            case 4:
                text = 'Strong';
                color = 'success';
                break;
            case 5:
                text = 'Very Strong';
                color = 'success';
                break;
        }
        indicator.textContent = text;
        indicator.className = `password-strength text-${color}`;
    }
}

// Add smooth scrolling to all links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add loading indicators to forms
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitButton = this.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"></span> Processing...';
        }
    });
}); 