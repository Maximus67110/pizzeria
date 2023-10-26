import { Controller } from '@hotwired/stimulus';
import noUiSlider from 'nouislider';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://github.com/symfony/stimulus-bridge#lazy-controllers
*/
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        min: Number,
        max: Number,
    }

    connect() {
        const cartCount = document.getElementById('cartCount');
        document.querySelectorAll("#addCart")
            .forEach((button) => {
                button.addEventListener("click", async function (e) {
                    e.preventDefault();
                    let productId = button.dataset.product;
                    let quantity = button.parentElement.parentElement.firstElementChild.firstElementChild.value;
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

        const slider = document.getElementById('slider');
        noUiSlider.create(slider, {
            start: [this.minValue, this.maxValue],
            connect: true,
            range: {
                'min': 0,
                'max': 100,
            },
            step: 1,
            behaviour: 'tap-drag',
            tooltips: true
        });
        slider.noUiSlider.on("update", this.refresh);
    }

    refresh(values) {
        const minPrice = document.getElementById('minPrice');
        const maxPrice = document.getElementById('maxPrice');
        minPrice.value = Math.round(values[0]);
        maxPrice.value = Math.round(values[1]);
    }
}
