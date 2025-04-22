<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <title>Título da página</title>
    <meta charset="utf-8">
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  </head>
  <body>
    Aqui vai o código HTML que fará seu site aparecer.
  </body>
  <script>
  
	function getUserAccount() {
		return axios({
		  url: 'https://www.e-prepag.com.br/ajax/garena/verificaProduto.php',
		  method: "post",
		  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
		  data: {vde: 111111111, codigo: "32570333675697746885", garena: "6443174696", valid: true, type: "pdv", verifica: true, token: "03AIIukzjYFD7HvyKfMewmOvAZ9QZDulauGRfSI-1_8W0oQb8phcw7qmgWvsWOXV99H4IGjjhLKZpfwIwVAmmP1XxRQAUAnxyXL_3o3IkuSxeNIXWuwEnEwoLpDckzcdt9ch9ECDlfAOpTDX5YQT2pKtCsbXv1BxHnJe1749o_WZPQTuXey8FAFrkLiOFyTkABNMYQR3wl6vc35YPvNu22WPR96ZO3iiifyspeaZk_wmiq9NeKp4b3rY5J5vLNu35Nr1AqQUw-TnfD3VlWbzAYws7DcbthpPfxjN3pzgbPUWBUwljzVh2LUmvKTIoOW5xv2ApCOgi0TBZ49p9OM09kx9SzQJ7grsoYDnkVxjVrJ_AgoVfhUEft3fgKKLkVuMg6iVue_p6aZme_gZeKBIkbgrHmPoP6SezyiFVtN_40SHtKK-elJ4ofYUD5AcEE6DomoZ6KAPEGYI_vi9SJtkGzeJFUdJvSz4Da9L8MuPJ2gkHSWoCHCOba-xL0i16zrLC6fSEOF8Woq4tqGQh-NKylMtqQMCA6IG0vfg"}
	    });
	}

	function getUserPermissions() {
	    return axios({
		  url: 'https://www.e-prepag.com.br/ajax/garena/verificaProduto.php',
		  method: "post",
		  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
		  data: {vde: 111111111, codigo: "32570333675697746885", garena: "6443174696", valid: true, type: "pdv", verifica: true, token: "03AIIukzjYFD7HvyKfMewmOvAZ9QZDulauGRfSI-1_8W0oQb8phcw7qmgWvsWOXV99H4IGjjhLKZpfwIwVAmmP1XxRQAUAnxyXL_3o3IkuSxeNIXWuwEnEwoLpDckzcdt9ch9ECDlfAOpTDX5YQT2pKtCsbXv1BxHnJe1749o_WZPQTuXey8FAFrkLiOFyTkABNMYQR3wl6vc35YPvNu22WPR96ZO3iiifyspeaZk_wmiq9NeKp4b3rY5J5vLNu35Nr1AqQUw-TnfD3VlWbzAYws7DcbthpPfxjN3pzgbPUWBUwljzVh2LUmvKTIoOW5xv2ApCOgi0TBZ49p9OM09kx9SzQJ7grsoYDnkVxjVrJ_AgoVfhUEft3fgKKLkVuMg6iVue_p6aZme_gZeKBIkbgrHmPoP6SezyiFVtN_40SHtKK-elJ4ofYUD5AcEE6DomoZ6KAPEGYI_vi9SJtkGzeJFUdJvSz4Da9L8MuPJ2gkHSWoCHCOba-xL0i16zrLC6fSEOF8Woq4tqGQh-NKylMtqQMCA6IG0vfg"}
	    });
	}

	Promise.all([getUserAccount(), getUserPermissions(),getUserAccount(), getUserPermissions(),getUserAccount(), getUserPermissions()])
	  .then(function (results) {
		const acct = results[0];
		const perm = results[1];
		
		console.log(results);
	});  
	
	
  </script>
</html>