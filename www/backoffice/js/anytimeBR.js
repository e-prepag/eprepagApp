var rangeDemoFormat = "%d/%m/%Y %H:%i";
var rangeDemoConv = new AnyTime.Converter({format:rangeDemoFormat});

function teste(input) {
	
	const d = new Date();
	let year = d.getFullYear();
	
	$(input).AnyTime_noPicker().AnyTime_picker({baseYear: 2008,
			earliest: rangeDemoConv.format(new Date(2008,1,1,0,0,0)),
			format: rangeDemoFormat,
			latest: rangeDemoConv.format(new Date(year,11,31,23,59,59)),
			dayAbbreviations: ['DOM','SEG','TER','QUA','QUI','SEX','SAB'],
			labelDayOfMonth: 'Dia do Mês',
			labelHour: 'Hora',
			labelMinute: 'Minuto',
			labelMonth: 'Mês',
			labelTitle: 'Selecione a Data e Hora',
			labelYear: 'Ano',
			monthAbbreviations: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
		}).focus();
  };
