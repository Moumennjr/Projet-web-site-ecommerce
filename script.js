document.addEventListener('DOMContentLoaded', () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const itemId = button.getAttribute('data-id');
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Article ajouté au panier !');
                } else {
                    alert('Erreur : ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s\'est produite.');
            });
        });
    });
});