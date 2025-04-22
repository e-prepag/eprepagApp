jQuery.fn.table2CSV = function(options) {
    var options = jQuery.extend({
        separator: ';',
        header: [],
        delivery: 'popup' // popup, value
    },
    options);

    var csvData = [];
    var headerArr = [];
    var el = this;

    //header
    var numCols = options.header.length;
    var tmpRow = []; // construct header avalible array

    if (numCols > 0) {
        for (var i = 0; i < numCols; i++) {
            tmpRow[tmpRow.length] = formatData(options.header[i]);
        }
    } else {
        $(el).filter(':visible').find('th').each(function() {
            if ($(this).css('display') != 'none') tmpRow[tmpRow.length] = formatData($(this).html());
        });
    }

    row2CSV(tmpRow);
    var col = "";
    // actual data
    $(el).find('.trListagem').each(function() {
        var tmpRow = [];
        $(this).filter(':visible').find('td').each(function(index) {
            col = formatData($(this).html());
//            if(index == options.toStr){
            if(options.toStr.indexOf(index) >= 0)
            {
                col = "'"+col.replace('"',"").replace('"',"")+"'"; //o campo já vem com aspas duplas nas extremidades, para funcionar, é preciso remove-las dando replace 2x e depois inserir as aspas simples
            }
        if ($(this).css('display') != 'none') tmpRow[tmpRow.length] = col;
        });
        row2CSV(tmpRow);
    });
    if (options.delivery == 'popup') {
        var mydata = csvData.join('\n');
        return popup(mydata);
    } else {
        var mydata = csvData.join('\n');
        return mydata;
    }

    function row2CSV(tmpRow) {
        var tmp = tmpRow.join('') // to remove any blank rows
        if (tmpRow.length > 0 && tmp != '') {
            var mystr = tmpRow.join(options.separator);
            csvData[csvData.length] = mystr;
        }
    }
    function formatData(input) {
        // replace " with ?
        var regexp = new RegExp(/["]/g);
        var output = input.replace(regexp, "?");
        //HTML
        var regexp = new RegExp(/\<[^\<]+\>/g);
        var output = output.replace(regexp, "");
        if (output == "") return '';
        return '"' + output + '"';
    }
    function popup(data) {
        $(".downloadCsv").attr("download","report_e-prepag.csv");
        $(".downloadCsv").attr("href",'data:text/csv;charset=utf-8,'+escape(data));
    }
};