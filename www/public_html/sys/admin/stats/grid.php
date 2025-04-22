<!--
script type="text/javascript" src="grid/tablesorter.com/addons/pager/jquery.tablesorter.pager.js"></script>
<script type="text/javascript">
var script = '<?php echo $script; ?>';
$(document).ready(function() { 
	var tabselecionada= $( "#tabs" ).tabs( "option", "selected" );
	
	$.tablesorter.addParser({
		id: "fancyCurrency",
		is: function(s) {
			// return false so this parser is not auto detected 
			return false; 
		},
		format: function(s) {
		  //alert(s);
		  //s = s.replace(/[$,]/g,'');
		  s = s.replace('.','');
		  return $.tablesorter.formatFloat( s );
		},
		type: "numeric"
	});


	$.tablesorter.addParser({
		id: "fancyPercent",
		is: function(s) {
			// return false so this parser is not auto detected 
			return false; 
		},
		format: function(s) {
		  //s = s.replace('.','').substr(0,s.length-1); // alterado pois os valores podem vir com 2 casas decimais e eles possuem , e nao . a serem substituidos
		  s = s.replace(',','').substr(0,s.length);
		  return $.tablesorter.formatFloat( s );
		},
		type: "numeric"
	});	
	if(script === 'POS_stats_abas.php') {
		if(tabselecionada == 1 || tabselecionada == 0 ) {
			$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
				widthFixed: true, 
				widgets: ['zebra'],
				headers: { 
					3: { sorter: "fancyCurrency" },
					4: { sorter: "fancyPercent" }
				}
			}); 
			
		} else if(tabselecionada == 2 || tabselecionada == 11) {
			$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
				widthFixed: true, 
				widgets: ['zebra'],
				headers: { 
					5: { sorter: false },
					9: { sorter: "fancyCurrency" },
					10: { sorter: "fancyPercent" }
				}
			}); 	
		} else if(tabselecionada == 3 || tabselecionada == 5 || tabselecionada == 6 || tabselecionada == 8 || tabselecionada == 9) {
			$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
				widthFixed: true, 
				widgets: ['zebra'],
				headers: { 
					2: { sorter: "fancyCurrency" },
					3: { sorter: "fancyPercent" }
				}
			});
		} else if(tabselecionada == 4) {
			$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
				widthFixed: true, 
				widgets: ['zebra'],
				headers: { 
					3: { sorter: "fancyCurrency" },
					4: { sorter: "fancyPercent" }
				}
			});			
		} else if(tabselecionada == 7) {
			$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
				widthFixed: true, 
				widgets: ['zebra'],
				headers: { 
					1: { sorter: "fancyCurrency" },
					2: { sorter: "fancyPercent" }
				}
			});
		} else if(tabselecionada == 12) {
			$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
				widthFixed: true, 
				widgets: ['zebra'],
				headers: { 
					5: { sorter: "fancyCurrency" },
					6: { sorter: "fancyPercent" }
				}
			});			
		} else if(tabselecionada == 13 || tabselecionada == 14) {
			$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
				widthFixed: true, 
				widgets: ['zebra'],
				headers: { 
					6: { sorter: "fancyCurrency" },
					7: { sorter: "fancyPercent" }
				}
			});			
		} else {
		
		}
		$("#tabela-container-<?php echo $abanome;?>").tablesorterPager({container: $("#pager-<?php echo $abanome;?>")});
	
	} else if(script == 'Money_stats_abas.php') {
			if(tabselecionada == 1 || tabselecionada == 0 || tabselecionada == 7 || tabselecionada == 8  ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						3: { sorter: "fancyCurrency" },
						4: { sorter: "fancyPercent" }
					}
				}); 
			
			} else if(tabselecionada == 2 || tabselecionada == 3 || tabselecionada == 5  || tabselecionada == 6 ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" },
						3: { sorter: "fancyPercent" }
					}
				});
			} else if(tabselecionada == 4) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						1: { sorter: "fancyCurrency" },
						2: { sorter: "fancyPercent" }
					}
				});
			} else if(tabselecionada == 9) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						4: { sorter: false },
						8: { sorter: "fancyCurrency" },
						9: { sorter: "fancyPercent" }
					}
				}); 
			} else {
			}
			$("#tabela-container-<?php echo $abanome;?>").tablesorterPager({container: $("#pager-<?php echo $abanome;?>")});

	} else if(script == 'MoneyEx_stats_abas.php') {
			if(tabselecionada == 1 || tabselecionada == 0 || tabselecionada == 7 || tabselecionada == 8  ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						3: { sorter: "fancyCurrency" },
						4: { sorter: "fancyPercent" }
					}
				}); 
			
			} else if(tabselecionada == 2 || tabselecionada == 3 || tabselecionada == 5  || tabselecionada == 6 ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" },
						3: { sorter: "fancyPercent" }
					}
				});
			} else if(tabselecionada == 4) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" },
						3: { sorter: "fancyPercent" }
					}
				});
			} else if(tabselecionada == 9) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						4: { sorter: false },
						6: { sorter: "fancyCurrency" },
						7: { sorter: "fancyPercent" }
					}
				}); 
			} else {
			}
			$("#tabela-container-<?php echo $abanome;?>").tablesorterPager({container: $("#pager-<?php echo $abanome;?>")});			

	} else if(script == 'Site_stats_abas.php') {
			if(tabselecionada == 0 || tabselecionada == 1 || tabselecionada == 7 || tabselecionada == 8) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						3: { sorter: "fancyCurrency" },
						4: { sorter: "fancyPercent" }
					}
				}); 			
			} else if(tabselecionada == 2 || tabselecionada == 3 || tabselecionada == 5 || tabselecionada == 6 ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" },
						3: { sorter: "fancyPercent" }
					}
				});
			} else if(tabselecionada == 4) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" }
					}
				});
			} else if(tabselecionada == 9) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						4: { sorter: false },
						8: { sorter: "fancyCurrency" },
						9: { sorter: "fancyPercent" }
					}
				}); 

			} else {
			}
			$("#tabela-container-<?php echo $abanome;?>").tablesorterPager({container: $("#pager-<?php echo $abanome;?>")});			

	} else if(script == 'LHMoney_stats_abas.php') {
			if(tabselecionada == 1 || tabselecionada == 0 ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						3: { sorter: "fancyCurrency" },
						4: { sorter: "fancyPercent" }
					}
				}); 
			} else if(tabselecionada == 2 || tabselecionada == 3 || tabselecionada == 4 || tabselecionada == 6 || tabselecionada == 7 ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" },
						3: { sorter: "fancyPercent" }
					}
				});				
			} else {
			}
			$("#tabela-container-<?php echo $abanome;?>").tablesorterPager({container: $("#pager-<?php echo $abanome;?>")});			
	
	} else if(script == 'Cartoes_stats_abas.php') {
			if(tabselecionada == 1 || tabselecionada == 0 ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						3: { sorter: "fancyCurrency" },
						4: { sorter: "fancyPercent" }
					}
				}); 	
			} else if(tabselecionada == 2) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						4: { sorter: false },
						8: { sorter: "fancyCurrency" },
						9: { sorter: "fancyPercent" }
					}
				}); 
			} else if(tabselecionada == 8) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" }
					}
				}); 				
			} else if(tabselecionada == 3 || tabselecionada == 4 || tabselecionada == 5 || tabselecionada == 6 || tabselecionada == 9 || tabselecionada == 10 ) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" },
						3: { sorter: "fancyPercent" }
					}
				});	
			} else if(tabselecionada == 7) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						2: { sorter: "fancyCurrency" }
					}
				});		
			} else if(tabselecionada == 9 || tabselecionada == 10) {
				$("#tabela-container-<?php echo $abanome;?>").tablesorter({ 
					widthFixed: true, 
					widgets: ['zebra'],
					headers: { 
						1: { sorter: false },
						5: { sorter: "fancyCurrency" },
						6: { sorter: "fancyPercent" }
					}
				}); 				
			} else {
			}
			$("#tabela-container-<?php echo $abanome;?>").tablesorterPager({container: $("#pager-<?php echo $abanome;?>")});
	} else {
	}

}); 
</script
-->

<div id="main">
    <table name="tabela-container-<?php echo $abanome;?>" id="tabela-container-<?php echo $abanome;?>" cellspacing="1" class="tablesorter">
        <?php echo $retorno;?>
    </table>
</div>