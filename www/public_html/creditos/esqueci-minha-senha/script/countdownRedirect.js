	const totalTime = 5;
	let timeLeft = totalTime;
			
	const countdownElement = document.querySelector('#countdown');
	countdownElement.textContent = timeLeft;
			
	const interval = setInterval(()=> {
				
		timeLeft--;
		countdownElement.textContent = timeLeft;
					
		if (timeLeft == 0) {
			clearInterval(interval);
			window.location.href = '/creditos/login.php';
		}
				
	}, 1000);