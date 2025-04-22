/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var currentDate = new Date(); 
 

    jQuery(function(e){
        e.datepicker.regional["pt-BR"]={
            closeText:"Fechar",
            prevText:"&#x3C;Anterior",
            nextText:"Próximo&#x3E;",
            currentText:"Hoje",
            monthNames:["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],
            monthNamesShort:["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],
            dayNames:["Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sábado"],
            dayNamesShort:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],
            dayNamesMin:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],
            weekHeader:"Sm",
            dateFormat:"dd/mm/yy",
            firstDay:0,
            isRTL:!1,
            showMonthAfterYear:!1,
            yearSuffix:""},e.datepicker.setDefaults(e.datepicker.regional["pt-BR"])
    });
/*
 * 
    funcao para setar intervalo máximo de datas no datepicker
    @parans:iptInicial = id do input que conterá a data inicial
            iptFinal = id do input que conterá a data final
            interval = intervalo de meses máximo permitido

Exemplo de uso:
    $(function(){

        var optDate = new Object();
            optDate.interval = 1;

        setDateInterval('tf_data_conf_ini','tf_data_conf_fim',optDate);
    });

*/
function setDateInterval(iptInicial,iptFinal,opt){
    var objDatePicker = new Object();
    
    opt = opt || "";
    
    objDatePicker.interval = (Object.prototype.hasOwnProperty.call(opt, 'interval')) ? opt["interval"] : 6;
    objDatePicker.maxDate = (Object.prototype.hasOwnProperty.call(opt, 'maxDate')) ? opt["maxDate"] : "dateToday";
    objDatePicker.dateFormat = (Object.prototype.hasOwnProperty.call(opt, 'dateFormat')) ? opt["dateFormat"] : "dd/mm/yy";
    objDatePicker.minDate = (Object.prototype.hasOwnProperty.call(opt, 'minDate')) ? opt["minDate"] : null;
    objDatePicker.changeMonth = (Object.prototype.hasOwnProperty.call(opt, 'changeMonth')) ? opt["changeMonth"] : true;

    objDatePicker.onClose = function(selectedDate, instance)
    {
        if (selectedDate != '') {
                $("#"+iptFinal).datepicker("option", "minDate", selectedDate);
                var date = $.datepicker.parseDate(instance.settings.dateFormat, selectedDate, instance.settings);
                date.setMonth(date.getMonth() + objDatePicker.interval);
                if(date > currentDate)
                    date = currentDate;
                $("#"+iptFinal).datepicker("option", "minDate", selectedDate);
                $("#"+iptFinal).datepicker("option", "maxDate", date);
            }
    };
    
    $("#"+iptInicial).datepicker(objDatePicker);

    var data = $("#"+iptInicial).datepicker("getDate");
    if(data){
        var tmpData = data;
        tmpData.setMonth(tmpData.getMonth()+objDatePicker.interval);
        
        if(tmpData <= currentDate)
            data.setMonth(tmpData.getMonth());
        else
            data = currentDate;
    }else
        data = currentDate;
    
    $("#"+iptFinal).datepicker({
        maxDate: data,
        changeMonth: true,
        dateFormat: objDatePicker.dateFormat,
        minDate: $("#"+iptInicial).datepicker("getDate")
    });
}