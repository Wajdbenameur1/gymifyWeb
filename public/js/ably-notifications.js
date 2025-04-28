/**
 * Ably Real-time Notifications
 * This script handles real-time notifications using the Ably service.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Ably client with the API key
    const ably = new Ably.Realtime('WWlNRQ.NtFTkw:nUpGoNHsYuuGPEGaiG7MFXg-VnDJCA-huFa182u2_0c');
    
    // Get the current user ID if available
    const currentUserId = document.body.dataset.userId || null;
    
    // Initialize notification count
    let notificationCount = 0;
    const notificationBadge = document.getElementById('notification-badge');
    const notificationList = document.getElementById('notification-list');
    
    // Clear notifications button
    const clearNotificationsBtn = document.getElementById('clear-notifications');
    if (clearNotificationsBtn) {
        clearNotificationsBtn.addEventListener('click', function() {
            notificationCount = 0;
            updateNotificationBadge();
            if (notificationList) {
                notificationList.innerHTML = '<li class="dropdown-item text-center text-muted">Aucune notification</li>';
            }
        });
    }
    
    // Function to update notification badge
    function updateNotificationBadge() {
        if (notificationBadge) {
            notificationBadge.textContent = notificationCount;
            notificationBadge.classList.toggle('d-none', notificationCount === 0);
        }
    }
    
    // Function to add notification to dropdown
    function addNotificationToDropdown(content, link) {
        if (!notificationList) return;
        
        // Remove "no notifications" message if it exists
        const emptyMessage = notificationList.querySelector('.text-muted');
        if (emptyMessage) {
            notificationList.removeChild(emptyMessage);
        }
        
        // Create new notification item
        const notificationItem = document.createElement('li');
        notificationItem.className = 'dropdown-item notification-item';
        
        // Create notification link
        const notificationLink = document.createElement('a');
        notificationLink.href = link;
        notificationLink.className = 'text-decoration-none text-dark';
        notificationLink.innerHTML = content;
        
        notificationItem.appendChild(notificationLink);
        
        // Add notification to the top of the list
        notificationList.insertBefore(notificationItem, notificationList.firstChild);
        
        // Limit the number of notifications (keep latest 10)
        const items = notificationList.querySelectorAll('.notification-item');
        if (items.length > 10) {
            for (let i = 10; i < items.length; i++) {
                notificationList.removeChild(items[i]);
            }
        }
    }
    
    // Function to show a toast notification
    function showToast(title, body, link) {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toastId = 'toast-' + Date.now();
        const toastEl = document.createElement('div');
        toastEl.id = toastId;
        toastEl.className = 'toast';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        
        // Create toast content
        toastEl.innerHTML = `
            <div class="toast-header">
                <strong class="me-auto">${title}</strong>
                <small>À l'instant</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${body}
            </div>
        `;
        
        // Add click handler to navigate to the link
        if (link) {
            toastEl.style.cursor = 'pointer';
            toastEl.addEventListener('click', function(e) {
                if (e.target.closest('.btn-close')) return; // Don't navigate if close button is clicked
                window.location.href = link;
            });
        }
        
        // Add to container and show
        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { 
            delay: 5000,
            autohide: true 
        });
        toast.show();
        
        // Remove toast from DOM after it's hidden
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastContainer.removeChild(toastEl);
        });
    }
    
    // Subscribe to post channel
    const postsChannel = ably.channels.get('posts');
    postsChannel.subscribe('new-post', function(message) {
        const post = message.data;
        
        // Don't show notification for current user's posts
        if (currentUserId && post.user && post.user.id == currentUserId) {
            return;
        }
        
        notificationCount++;
        updateNotificationBadge();
        
        // Create notification content
        const notificationContent = `
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-newspaper text-primary"></i>
                </div>
                <div class="flex-grow-1 ms-2">
                    <strong>${post.author}</strong> a publié un nouvel article:
                    <br><span class="text-primary">${post.title}</span>
                </div>
            </div>
        `;
        
        // Add to dropdown
        addNotificationToDropdown(notificationContent, `/post/${post.id}`);
        
        // Show toast
        showToast('Nouvel article publié', `${post.author} a publié "${post.title}"`, `/post/${post.id}`);
        
        // Optionally refresh the posts list if we're on the index page
        if (window.location.pathname === '/post/' || window.location.pathname === '/post') {
            const refreshPostsButton = document.getElementById('refresh-posts');
            if (refreshPostsButton) {
                refreshPostsButton.classList.remove('d-none');
                refreshPostsButton.addEventListener('click', function() {
                    window.location.reload();
                });
            }
        }
    });
    
    // Subscribe to comments channel
    const commentsChannel = ably.channels.get('comments');
    commentsChannel.subscribe('new-comment', function(message) {
        const comment = message.data;
        
        // Don't show notification for current user's comments
        if (currentUserId && comment.user && comment.user.id == currentUserId) {
            return;
        }
        
        notificationCount++;
        updateNotificationBadge();
        
        // Create notification content
        const notificationContent = `
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-comment text-success"></i>
                </div>
                <div class="flex-grow-1 ms-2">
                    <strong>${comment.user.nom}</strong> a commenté sur:
                    <br><span class="text-primary">${comment.postTitle}</span>
                </div>
            </div>
        `;
        
        // Add to dropdown
        addNotificationToDropdown(notificationContent, `/post/${comment.postId}`);
        
        // Show toast
        showToast('Nouveau commentaire', `${comment.user.nom} a commenté l'article "${comment.postTitle}"`, `/post/${comment.postId}`);
        
        // If we're currently viewing the post, update the comments section
        if (window.location.pathname === `/post/${comment.postId}`) {
            // Refresh the comments or add the new comment dynamically
            const refreshCommentsButton = document.getElementById('refresh-comments');
            if (refreshCommentsButton) {
                refreshCommentsButton.classList.remove('d-none');
                refreshCommentsButton.addEventListener('click', function() {
                    window.location.reload();
                });
            }
        }
    });
    
    // Subscribe to reactions channel
    const reactionsChannel = ably.channels.get('reactions');
    reactionsChannel.subscribe('new-reaction', function(message) {
        const reaction = message.data;
        
        console.log('Received reaction via Ably:', reaction);
        
        // Don't show notification for current user's reactions
        if (currentUserId && reaction.user && reaction.user.id == currentUserId) {
            console.log('This is the current user\'s reaction, ignoring notification');
            return;
        }
        
        // Si cette notification concerne un post pour lequel l'utilisateur actuel a une réaction en cours,
        // mettre à jour juste le compteur mais pas l'icône
        const hasPendingReaction = window.pendingReactions && window.pendingReactions[reaction.postId];
        
        notificationCount++;
        updateNotificationBadge();
        
        // Create notification content
        const actionText = reaction.isNew ? 'a réagi' : 'a changé sa réaction';
        const reactionEmoji = reaction.reactionEmoji;
        
        const notificationContent = `
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <span style="font-size: 1.2rem;">${reactionEmoji}</span>
                </div>
                <div class="flex-grow-1 ms-2">
                    <strong>${reaction.user.nom}</strong> ${actionText} avec ${reactionEmoji} sur:
                    <br><span class="text-primary">${reaction.postTitle}</span>
                </div>
            </div>
        `;
        
        // Add to dropdown
        addNotificationToDropdown(notificationContent, `/post/${reaction.postId}`);
        
        // Show toast
        showToast('Nouvelle réaction', `${reaction.user.nom} ${actionText} ${reactionEmoji} sur "${reaction.postTitle}"`, `/post/${reaction.postId}`);
        
        // Get the updated reaction counts for use in all cases
        const counts = reaction.counts || {};
        
        // Update UI differently based on whether we're viewing the post and have a pending reaction
        if (window.location.pathname === `/post/${reaction.postId}`) {
            console.log('Updating reactions for current post view');
            
            // Si l'utilisateur courant a une réaction en cours, ne pas écraser son icône
            if (hasPendingReaction) {
                console.log('User has a pending reaction, only updating counts');
                
                // Mettre à jour uniquement les compteurs, pas l'icône
                const totalReactions = Object.values(counts).reduce((sum, count) => sum + count, 0);
                
                // Update just the counts
                const countElements = document.querySelectorAll(`.btn-react-toggle[data-post-id="${reaction.postId}"] .reaction-count`);
                countElements.forEach(el => {
                    el.textContent = totalReactions;
                });
                
                // Update reaction summary if it exists
                if (typeof window.updateReactionSummaries === 'function') {
                    window.updateReactionSummaries(reaction.postId, counts);
                }
            } else {
                // Call the updateReactionUI function from ably-reactions.js if it exists
                if (typeof window.updateReactionUI === 'function') {
                    console.log('Using global updateReactionUI function');
                    // Pass null for userReaction to avoid changing current user's reaction
                    window.updateReactionUI(reaction.postId, null, counts);
                } else {
                    console.log('Using fallback reaction UI update');
                    
                    // Find all buttons for this post 
                    const buttons = document.querySelectorAll(`.btn-react-toggle[data-post-id="${reaction.postId}"]`);
                    
                    buttons.forEach(button => {
                        // Update total reaction count on the button
                        const totalReactions = Object.values(counts).reduce((sum, count) => sum + count, 0);
                        const countElement = button.querySelector('.reaction-count');
                        if (countElement) {
                            countElement.textContent = totalReactions;
                        }
                    });
                    
                    // Update reaction summary counts if they exist
                    const summaries = document.querySelectorAll(`.reactions-summary[data-post-id="${reaction.postId}"]`);
                    
                    summaries.forEach(summary => {
                        // Update individual reaction type counts
                        Object.entries(counts).forEach(([type, count]) => {
                            const iconElement = summary.querySelector(`.react-icon.reaction-${type}`);
                            if (iconElement) {
                                const countElement = iconElement.querySelector('.react-count');
                                if (countElement) {
                                    countElement.textContent = count > 0 ? count : '';
                                }
                                
                                // Show/hide based on count
                                iconElement.style.display = count > 0 ? 'inline-flex' : 'none';
                            }
                        });
                    });
                }
            }
        } else {
            console.log('Not on the post view; updating only counts on index page');
            
            // Update only counts on index page, preserving user reactions
            if (hasPendingReaction) {
                console.log('User has a pending reaction on index page, only updating counts');
                // Update just the counts without changing the reaction icon
                const totalReactions = Object.values(counts).reduce((sum, count) => sum + count, 0);
                
                const countElements = document.querySelectorAll(`.btn-react-toggle[data-post-id="${reaction.postId}"] .reaction-count`);
                countElements.forEach(el => {
                    el.textContent = totalReactions;
                });
                
                // Update reaction summary if it exists
                if (typeof window.updateReactionSummaries === 'function') {
                    window.updateReactionSummaries(reaction.postId, counts);
                }
            } else {
                // We're not on the post page and don't have a pending reaction
                // This could be a user viewing the index page who has not reacted
                // Update counts but don't change reaction status
                const totalReactions = Object.values(counts).reduce((sum, count) => sum + count, 0);
                
                const countElements = document.querySelectorAll(`.btn-react-toggle[data-post-id="${reaction.postId}"] .reaction-count`);
                countElements.forEach(el => {
                    el.textContent = totalReactions;
                });
            }
        }
    });
}); 