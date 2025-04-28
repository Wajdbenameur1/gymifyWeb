/**
 * Ably Reactions Handler
 * This script handles post reactions and integrates with Ably for real-time updates.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Toggle reaction bar on button click
    document.querySelectorAll('.btn-react-toggle').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const reactionBar = this.nextElementSibling?.classList.contains('reaction-bar') 
                ? this.nextElementSibling 
                : this.parentElement.querySelector('.reaction-bar');
                
            if (reactionBar) {
                reactionBar.classList.toggle('d-none');
                
                // Close bar when clicking outside
                document.addEventListener('click', function closeBar(e) {
                    if (!reactionBar.contains(e.target) && e.target !== btn) {
                        reactionBar.classList.add('d-none');
                        document.removeEventListener('click', closeBar);
                    }
                });
            }
        });
    });
    
    // Handle reaction click
    document.querySelectorAll('.react-option').forEach(option => {
        option.addEventListener('click', function() {
            const reactionBar = this.closest('.reaction-bar');
            const postId = this.closest('.post-buttons-container').querySelector('.btn-react-toggle').dataset.postId;
            const csrfToken = this.closest('.post-buttons-container').querySelector('.btn-react-toggle').dataset.csrfToken;
            const reactionType = this.dataset.type;
            
            if (postId && csrfToken && reactionType) {
                submitReaction(postId, reactionType, csrfToken, this);
            }
            
            if (reactionBar) {
                reactionBar.classList.add('d-none');
            }
        });
    });
    
    // Function to submit reaction via AJAX
    function submitReaction(postId, type, token, reactionElement) {
        const formData = new FormData();
        formData.append('type', type);
        formData.append('_token', token);
        
        fetch(`/reaction/${postId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            updateReactionUI(postId, data.userReaction, data.counts);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Function to update UI based on reaction response
    function updateReactionUI(postId, userReaction, counts) {
        // Find all instances of this post (could be in multiple places like feed and modal)
        const reactionButtons = document.querySelectorAll(`.btn-react-toggle[data-post-id="${postId}"]`);
        
        reactionButtons.forEach(button => {
            // Calculate total reactions
            let totalReactions = 0;
            Object.values(counts).forEach(count => {
                totalReactions += count;
            });
            
            // Update button text and class
            const countsElement = button.querySelector('.reaction-count');
            if (countsElement) {
                countsElement.textContent = totalReactions;
            }
            
            // Remove any existing reaction classes
            button.classList.remove('active');
            const existingReactionSpan = button.querySelector('span[class^="reaction-"]');
            if (existingReactionSpan) {
                existingReactionSpan.remove();
            }
            
            // Add or restore the default icon
            let iconElement = button.querySelector('i.far');
            if (!iconElement && !userReaction) {
                iconElement = document.createElement('i');
                iconElement.className = 'far fa-thumbs-up';
                button.prepend(iconElement);
            } else if (iconElement && userReaction) {
                iconElement.remove();
            }
            
            // Update with new reaction if any
            if (userReaction) {
                button.classList.add('active');
                const reactionSpan = document.createElement('span');
                reactionSpan.className = `reaction-${userReaction}`;
                reactionSpan.textContent = getReactionEmoji(userReaction);
                button.prepend(reactionSpan);
            }
        });
        
        // Also update any reaction summaries for this post
        updateReactionSummaries(postId, counts);
    }
    
    // Function to update reaction summaries in the UI
    function updateReactionSummaries(postId, counts) {
        const summaries = document.querySelectorAll(`.reactions-summary[data-post-id="${postId}"]`);
        
        summaries.forEach(summary => {
            const icons = summary.querySelectorAll('.react-icon');
            
            // Update counts for each reaction type
            Object.entries(counts).forEach(([type, count]) => {
                // Find icon for this type
                const icon = Array.from(icons).find(icon => icon.classList.contains(`reaction-${type}`));
                if (icon) {
                    const countEl = icon.querySelector('.react-count');
                    if (countEl) {
                        countEl.textContent = count > 0 ? count : '';
                        icon.style.display = count > 0 ? 'inline-flex' : 'none';
                    }
                }
            });
        });
    }
    
    // Helper function to get reaction emoji
    function getReactionEmoji(type) {
        const emojis = {
            'like': '👍',
            'love': '❤️',
            'haha': '😂',
            'wow': '😮',
            'sad': '😢',
            'angry': '😡'
        };
        
        return emojis[type] || '';
    }
}); 