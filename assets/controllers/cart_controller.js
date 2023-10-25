import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const cartCount = document.getElementById('cartCount');
        document.querySelectorAll("#addCart")
            .forEach((button) => {
                button.addEventListener("click", async function (e) {
                    e.preventDefault();
                    let productId = button.dataset.product;
                    let quantity = button.previousElementSibling.value;
                    fetch(`/cart?quantity=${quantity}&productId=${productId}`, {
                        method: "POST",
                    })
                    .then(response => response.text())
                    .then(value => {
                        button.innerText = 'Update';
                        cartCount.innerText = value;
                    });
                });
            });
    }
}
