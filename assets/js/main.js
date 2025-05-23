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
                        <div class="comment" id="comment-${comment.id}">
                            <div class="d-flex justify-content-between">
                                <strong>${comment.user_name}</strong>
                                <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
                            </div>
                            <p class="mb-0 comment-text">${comment.comment_text}</p>
                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-sm btn-primary me-2 edit-comment" data-comment-id="${comment.id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-comment" data-comment-id="${comment.id}">Delete</button>
                            </div>
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

    // Edit comment functionality
    document.querySelectorAll('.edit-comment').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.dataset.commentId;
            const commentCard = document.getElementById(`comment-${commentId}`);
            const commentText = commentCard.querySelector('.comment-text');
            const currentText = commentText.innerText.trim();
            
            // Create edit interface
            const editForm = document.createElement('div');
            editForm.innerHTML = `
                <textarea class="form-control mb-2">${currentText}</textarea>
                <div class="btn-group">
                    <button class="btn btn-sm btn-success save-edit">Save</button>
                    <button class="btn btn-sm btn-secondary cancel-edit">Cancel</button>
                </div>
            `;
            
            // Hide the original content
            commentText.style.display = 'none';
            this.closest('.btn-group').style.display = 'none';
            
            // Insert edit form
            commentText.parentNode.insertBefore(editForm, commentText.nextSibling);
            
            // Save button handler
            editForm.querySelector('.save-edit').addEventListener('click', async function() {
                const newText = editForm.querySelector('textarea').value.trim();
                const formData = new FormData();
                formData.append('action', 'edit');
                formData.append('comment_id', commentId);
                formData.append('comment_text', newText);
                
                try {
                    const response = await fetch('api/manage_comment.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        commentText.innerHTML = newText.replace(/\n/g, '<br>');
                        editForm.remove();
                        commentText.style.display = '';
                        button.closest('.btn-group').style.display = '';
                    } else {
                        alert(result.error || 'Error updating comment');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error updating comment');
                }
            });
            
            // Cancel button handler
            editForm.querySelector('.cancel-edit').addEventListener('click', function() {
                editForm.remove();
                commentText.style.display = '';
                button.closest('.btn-group').style.display = '';
            });
        });
    });
    
    // Delete comment functionality
    document.querySelectorAll('.delete-comment').forEach(button => {
        button.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            const commentId = this.dataset.commentId;
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('comment_id', commentId);
            
            try {
                const response = await fetch('api/manage_comment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    document.getElementById(`comment-${commentId}`).remove();
                } else {
                    alert(result.error || 'Error deleting comment');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error deleting comment');
            }
        });
    });

    // Smooth collapse animation for mobile menu
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    navbarToggler.addEventListener('click', function() {
        document.body.classList.toggle('nav-open');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navbarCollapse.contains(e.target) && !navbarToggler.contains(e.target)) {
            navbarCollapse.classList.remove('show');
            document.body.classList.remove('nav-open');
        }
    });

    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navbarCollapse.classList.remove('show');
            document.body.classList.remove('nav-open');
        });
    });
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

// Mobile menu handling
document.addEventListener('DOMContentLoaded', function() {
    // Smooth collapse animation for mobile menu
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');

    if (navbarToggler && navbarCollapse) {
        navbarToggler.addEventListener('click', function() {
            document.body.classList.toggle('nav-open');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navbarCollapse.contains(e.target) && !navbarToggler.contains(e.target)) {
                navbarCollapse.classList.remove('show');
                document.body.classList.remove('nav-open');
            }
        });

        // Close mobile menu when clicking on a link
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navbarCollapse.classList.remove('show');
                document.body.classList.remove('nav-open');
            });
        });
    }
});