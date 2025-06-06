<style>

.modal-promocao {
    border-radius: 7px;
    box-shadow: 0px 0px 4px 0px #000;
    color: #0d0d0d;
    position: fixed;
    display: flex;
    justify-content: center;
    align-items: center;
    top: 50%;
    left: 50%;
    width: 70vw;
    height: 70vh;
    transform: translate(-50%, -50%);
    z-index: 999999999999;
    opacity: 0;
    background: #f3f3f3;
}

.modal-promocao-body {
	position: relative;
	padding: 10px;
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 75%;
    align-items: center;
    justify-content: space-evenly;
    border-radius: 7px;
    flex-wrap: wrap;
}

.modal-promocao-body p {
    margin: 0;
    font-size: 2rem;
    text-transform: uppercase;
    line-height: 150%;
    text-align: left;
}

span.destaque-secundario {
    font-weight: bold;
    color: #268fbd;
}

span.destaque-principal {
    color: #0eb725;
    font-weight: bold;
    font-size: 2.4rem;
}

button.fecha-modal {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #226886;
	border: unset;
    border-radius: 100%;
    font-size: 15px;
    color: #fff;
    z-index: 9999999999999999999;

}


@keyframes show-modal {
	0% {
		opacity: 0;
	}
	100% {
		opacity: 1;		
	}
}

@keyframes hide-modal {
	0% {
		opacity: 1;
	}
	50% {
		opacity: 0;
	}
	100% {
		display: none;
	}
}

@media screen and (max-width: 800px) {
	.modal-promocao-body p {
		font-size: 1.3rem;
		
		text-align: center;
	}
	
	button.btn-promocao {
		font-size: 1.3rem;
	}
}

</style>

<div class="modal-promocao">

		<button class="fecha-modal" >X</button>
		
	<div class="modal-promocao-body">
	
		<p><span class="destaque-principal">Bônus de 20% em Wcoin! &#129321;</span></p>

		<p>Aproveite e divulgue para seus clientes, imagens de divulgação disponível em seu e-mail.</p>

		<p>Ah, e lembrando: <span class="destaque-secundario">para quem participou do último evento</span>, a E-Prepag aumentou o limite de vocês! &#129297;</p>

		<p>Promoção válida de 30/01/2024 a 06/02/2024.</p>

		<p>Boas vendas! &#129392;&#65039;</p>
	
	</div>
</div>

<script>
	const modal_promocao = document.querySelector('.modal-promocao');
	const fecha_modal = document.querySelector('.fecha-modal');
	
	window.addEventListener("load", (event) => {
		setTimeout(() => {
			modal_promocao.style.animation = 'show-modal 1s forwards';
		}, 1000);
	});

	fecha_modal.addEventListener('click', (event) => {
		modal_promocao.style.animation = 'hide-modal 1s forwards';
	});
</script>