function validateQuantity(input) {
    const selector = input.closest('.quantity-selector');
    const min = parseInt(selector.dataset.min) || 0;
    const max = parseInt(selector.dataset.max) || Infinity;
    let value = parseInt(input.value);

    if (isNaN(value) || value < min) {
        value = min;
    } else if (value > max) {
        value = max;
    }

    input.value = value;
    updateButtonStates(selector);
}

function changeQuantity(button, change) {
    const selector = button.closest('.quantity-selector');
    const input = selector.querySelector('.quantity-input');
    const minusBtn = selector.querySelector('.quantity-btn:first-child');
    const plusBtn = selector.querySelector('.quantity-btn:last-child');

    const min = parseInt(selector.dataset.min) || 0;
    const max = parseInt(selector.dataset.max) || Infinity;
    const currentValue = parseInt(input.value) || min;
    const newValue = Math.max(min, Math.min(max, currentValue + change));

    if (newValue !== currentValue) {
        input.value = newValue;

        // Animação de pulso
        selector.classList.add('pulse-animation');
        setTimeout(() => selector.classList.remove('pulse-animation'), 300);

        // Atualizar estado dos botões
        updateButtonStates(selector);
    }
}

function updateButtonStates(selector) {
    const input = selector.querySelector('.quantity-input');
    const minusBtn = selector.querySelector('.quantity-btn:first-child');
    const plusBtn = selector.querySelector('.quantity-btn:last-child');

    const min = parseInt(selector.dataset.min) || 0;
    const max = parseInt(selector.dataset.max) || Infinity;
    const value = parseInt(input.value);

    minusBtn.disabled = value <= min;
    plusBtn.disabled = value >= max;
}