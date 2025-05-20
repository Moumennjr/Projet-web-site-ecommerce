document.addEventListener('DOMContentLoaded', () => {
    // Gestion de l'ajout au panier (uniquement sur item.php)
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', () => {
            const itemId = button.getAttribute('data-id');
            const quantityInput = document.querySelector('#quantity');
            const quantity = quantityInput ? quantityInput.value : 1;

            button.disabled = true;
            button.textContent = 'Ajout...';

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.textContent = 'Ajouté !';
                    setTimeout(() => {
                        button.textContent = 'Ajouter';
                        button.disabled = false;
                    }, 1000);
                } else {
                    alert('Erreur : ' + data.message);
                    button.textContent = 'Ajouter';
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s\'est produite lors de l\'ajout au panier.');
                button.textContent = 'Ajouter';
                button.disabled = false;
            });
        });
    });

    // Validation du formulaire de recherche
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            const prixMin = searchForm.querySelector('input[name="prix_min"]').value;
            const prixMax = searchForm.querySelector('input[name="prix_max"]').value;

            if (prixMin && isNaN(prixMin)) {
                e.preventDefault();
                alert('Le prix minimum doit être un nombre valide.');
                return;
            }
            if (prixMax && isNaN(prixMax)) {
                e.preventDefault();
                alert('Le prix maximum doit être un nombre valide.');
                return;
            }
            if (prixMin && prixMax && Number(prixMin) > Number(prixMax)) {
                e.preventDefault();
                alert('Le prix minimum ne peut pas être supérieur au prix maximum.');
                return;
            }
        });
    }

    // Validation du formulaire d'inscription
    const registerForm = document.querySelector('#register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const nom = registerForm.querySelector('#nom').value;
            const prenom = registerForm.querySelector('#prenom').value;
            const numTel = registerForm.querySelector('#num_tel').value;
            const email = registerForm.querySelector('#email').value;
            const password = registerForm.querySelector('#password').value;
            const confirmPassword = registerForm.querySelector('#confirm_password').value;

            if (nom.length > 50) {
                e.preventDefault();
                alert('Le nom ne doit pas dépasser 50 caractères.');
                return;
            }

            if (prenom.length > 50) {
                e.preventDefault();
                alert('Le prénom ne doit pas dépasser 50 caractères.');
                return;
            }

            if (!/^[0-9]{10}$/.test(numTel)) {
                e.preventDefault();
                alert('Le numéro de téléphone doit contenir exactement 10 chiffres.');
                return;
            }

            if (email.length > 100 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('L\'adresse email est invalide ou trop longue (max 100 caractères).');
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return;
            }
        });
    }

    // Gestion des actions du panier
    // Valider une commande
    const validateButtons = document.querySelectorAll('.btn-validate');
    validateButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (!window.confirm('Êtes-vous sûr de vouloir valider cette commande ?')) {
                return;
            }

            const cartId = button.getAttribute('data-cart-id');
            button.disabled = true;
            button.textContent = 'Validation...';

            fetch('validate_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                    button.textContent = 'Valider';
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s\'est produite lors de la validation.');
                button.textContent = 'Valider';
                button.disabled = false;
            });
        });
    });

    // Supprimer un article du panier
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (!window.confirm('Êtes-vous sûr de vouloir supprimer cet article du panier ?')) {
                return;
            }

            const cartId = button.getAttribute('data-cart-id');
            button.disabled = true;
            button.textContent = 'Suppression...';

            fetch('delete_cart_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                    button.textContent = 'Supprimer';
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s\'est produite lors de la suppression.');
                button.textContent = 'Supprimer';
                button.disabled = false;
            });
        });
    });

    // Valider toutes les commandes
    const validateAllButton = document.querySelector('.btn-validate-all');
    if (validateAllButton) {
        validateAllButton.addEventListener('click', () => {
            if (!window.confirm('Êtes-vous sûr de vouloir valider toutes les commandes ?')) {
                return;
            }

            const userId = validateAllButton.getAttribute('data-user-id');
            validateAllButton.disabled = true;
            validateAllButton.textContent = 'Validation...';

            fetch('validate_all_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                    validateAllButton.textContent = validateAllButton.textContent.replace('Validation...', 'Valider tout');
                    validateAllButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s\'est produite lors de la validation.');
                validateAllButton.textContent = validateAllButton.textContent.replace('Validation...', 'Valider tout');
                validateAllButton.disabled = false;
            });
        });
    }

    // Supprimer tous les articles du panier
    const deleteAllButton = document.querySelector('.btn-delete-all');
    if (deleteAllButton) {
        deleteAllButton.addEventListener('click', () => {
            if (!window.confirm('Êtes-vous sûr de vouloir supprimer tous les articles du panier ?')) {
                return;
            }

            const userId = deleteAllButton.getAttribute('data-user-id');
            deleteAllButton.disabled = true;
            deleteAllButton.textContent = 'Suppression...';

            fetch('delete_all_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Erreur : ' + data.message);
                    deleteAllButton.textContent = 'Supprimer tout';
                    deleteAllButton.disabled = false;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur s\'est produite lors de la suppression.');
                deleteAllButton.textContent = 'Supprimer tout';
                deleteAllButton.disabled = false;
            });
        });
    }

    // Effet de survol sur les cartes produits
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'scale(1.02)';
            card.style.transition = 'transform 0.2s ease';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'scale(1)';
        });
    });

    // Effet de survol sur le produit mis en avant
    const featuredProduct = document.querySelector('.featured-product');
    if (featuredProduct) {
        featuredProduct.addEventListener('mouseenter', () => {
            featuredProduct.style.backgroundColor = '#F0F4F8';
            featuredProduct.style.transition = 'background-color 0.3s ease';
        });
        featuredProduct.addEventListener('mouseleave', () => {
            featuredProduct.style.backgroundColor = '#FFF';
        });
    }
});