// Initialisation des graphiques Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Forcer le rafraîchissement des graphiques
    if (typeof Chart !== 'undefined') {
        console.log('Chart.js is loaded');
        
        // Réinitialiser les Canvas pour s'assurer qu'ils sont propres
        document.querySelectorAll('canvas').forEach(canvas => {
            if (canvas.chart) {
                canvas.chart.destroy();
            }
        });
        
        // Attendre un court moment pour permettre au DOM de se mettre à jour
        setTimeout(function() {
            // Rafraîchir les graphiques
            if (window.renderChartjsElements) {
                window.renderChartjsElements();
                console.log('Charts rendered');
            } else {
                console.warn('renderChartjsElements function not found');
            }
        }, 100);
    } else {
        console.error('Chart.js is not loaded');
    }
}); 