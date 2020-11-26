(function ($)
{
    $.fn.heavyTable = function (params)
    {

        params = $.extend({
            startPosition: {
                x: 1,
                y: 1
            }
        }, params);

        var settings = jQuery.extend({
            param:'value'
        }, params);
        //Define a reference to our function myplugin which it's 
        //part of jQuery namespace functions, so we can use later
        //within inside functions
        var $jquery=this;

        this.each(function ()
        {
            var
                    $hTable = $(this).find('tbody'),
                    i = 0,
                    x = params.startPosition.x,
                    y = params.startPosition.y,
                    max = {
                        y: $hTable.find('tr').length,
                        x: $hTable.parent().find('th').length
                    };
//            $hTable.find('tr:nth-child(' + (max.y - 2) + ')').css('background-color', '#f5ddb2');
//            $hTable.find('tr:gt(' + (max.y - 6) + ')' + ':lt(' +  (max.y - 4) +')').css('background-color', '#f5ddb2');
//            $hTable.find('tr:nth-child(' + (max.y - 4) + '), tr:nth-child(' + (max.y - 2) + ')').css('background-color', '#f5edb2');
//            $hTable.find('tr:nth-child(' + (max.y - 3) + '), tr:nth-child(' + (max.y - 1) + ')').css('background-color', '#f5ddd2');
//            $hTable.find('tr:nth-child(' + (max.y) + ')').css('background-color', '#dddddd');

            //console.log(xMax + '*' + yMax);
            //alert("Here!");

            function clearCell()
            {
                $hTable.find('.selected input').each(function ()
                { });
                content = $hTable.find('.selected input').val();
                if (typeof (content) !== 'undefined')
                {
                    content = normalizeCell(content);
                    $hTable.find('.selected').html(content);
                    calcTotal();
                }
                $hTable.find('.selected').toggleClass('selected');
            }

            function normalizeCell(content)
            {
                content = parseInt(content, 10);
                if (content < 0)
                {
                    content = 0;
                }
                if (content > 31)
                {
                    content = 31;
                }
                return content;
            }

//            function calcTotal()
//            {
//                var cell = $($hTable.find('.selected')).closest('td');
//                var cellIndex = cell[0].cellIndex;
//                var row = cell.closest('tr');
//                var rowIndex = row[0].rowIndex;
//                var totTarRow = max.y - 4;
//                var totEffRow = max.y - 3;
//                var progTarRow = max.y - 2;
//                var progEffRow = max.y - 1;
//                var newTot = 0;
//
//                for (i = 2; i < (totTarRow); i++)
//                {
//                    newTot += parseInt($hTable.find('tr:nth-child(' + (i) + ')').find('td:nth-child(' + (cellIndex + 1) + ')').html());
//                }
//                $hTable.find('tr:nth-child(' + (totTarRow) + ')').find('td:nth-child(' + (cellIndex + 1) + ')').html(newTot);
//                var newProg = 0;
//                for (i = 2; i <= (max.x); i++)
//                {
//                    newProg += parseInt($hTable.find('tr:nth-child(' + (totTarRow) + ')').find('td:nth-child(' + (i) + ')').html());
//                    $hTable.find('tr:nth-child(' + (progTarRow) + ')').find('td:nth-child(' + (i) + ')').html(newProg);
//                }
//
//                for (i = 2; i < (totEffRow); i++)
//                {
//                    newTot += parseInt($hTable.find('tr:nth-child(' + (i) + ')').find('td:nth-child(' + (cellIndex + 1) + ')').html());
//                }
//                $hTable.find('tr:nth-child(' + (totEffRow) + ')').find('td:nth-child(' + (cellIndex + 1) + ')').html(newTot);
//                var newProg = 0;
//                for (i = 2; i <= (max.x); i++)
//                {
//                    newProg += parseInt($hTable.find('tr:nth-child(' + (totEffRow) + ')').find('td:nth-child(' + (i) + ')').html());
//                    $hTable.find('tr:nth-child(' + (progEffRow) + ')').find('td:nth-child(' + (i) + ')').html(newProg);
//                }
////        alert("Riga "+rowIndex+" Colonna "+cellIndex+" newTot "+newTot);
//                $hTable.find('.selected').html(content);
//            }

            function dispTarget()
            {
//                var totTarRow = max.y - 4;
//
//                alert("dispTarget");
//                for (i = 2; i < (totTarRow); i++)
//                {
//                    for (j = 2; j <= (max.x); j++)
//                    {
//                    var currHeadCell = $hTable.find('tr:first-child')
//                            .find('th:nth-child(' + (j - 1) + ')');
//                    var colHeader = currHeadCell.text();
//                    }
//                }
            }

            function dispEff()
            {
//                alert("dispEff");
            }
//
//            function displayCellData()
//            {
//                var nomiMesi = ["Gennaio", "Febbraio", "Marzo", "Aprile",
//                    "Maggio", "Giugno", "Luglio", "Agosto", "Settembre",
//                    "Ottobre", "Novembre", "Dicembre"];
//                var cell = $($hTable.find('.selected')).closest('td');
//                var cellIndex = cell[0].cellIndex;
//                var row = cell.closest('tr');
//                var rowIndex = row[0].rowIndex;
//                currHeadCell = $hTable.find('tr:first-child')
//                        .find('th:nth-child(' + (cellIndex + 1) + ')');
//                colHeader = currHeadCell.text();
//                monthYear = colHeader.split('/');
//                $("#dialog_title_span").text(
//                        ' Mese ' + monthYear[0] +
//                        ' Mese ' + nomiMesi[monthYear[0] - 1] +
//                        ' GG/Mese ' + daysInMonth(monthYear[1], monthYear[0]) +
//                        ' Anno ' + monthYear[1] +
//                        ' Nome ' + currData[rowIndex]['Nome'] +
//                        ' ' + currData[rowIndex]['Cognome'] +
//                        ' Contratto ' + currData[rowIndex]['Contratto'] +
//                        ' Sigla ' + currData[rowIndex]['Sigla']);
//
////                        'Riga = ' + rowIndex +
////                        '  Colonna = ' + cellIndex +
////                        ' Id ' + currData[rowIndex]['Id'] +
////                        ' HeadCell ' + colHeader +
//
////                $("#Nome").val(currData[rowIndex]['Nome']);
////                $("#Cognome").val(currData[rowIndex]['Cognome']);
//
//                $('#GiorniTarget').val(currData[rowIndex]['GGM'][colHeader]['GiorniTarget']);
//                $('#GiorniEff').val(currData[rowIndex]['GGM'][colHeader]['GiorniEff']);
//                $('#FerieEff').val(currData[rowIndex]['GGM'][colHeader]['FerieEff']);
//                $('#PermEff').val(currData[rowIndex]['GGM'][colHeader]['PermEff']);
//                $('#MalEff').val(currData[rowIndex]['GGM'][colHeader]['MalEff']);
//            }
//
//            function getCellData()
//            {
//                var cell = $($hTable.find('.selected')).closest('td');
//                var cellIndex = cell[0].cellIndex;
//                var row = cell.closest('tr');
//                var rowIndex = row[0].rowIndex;
//                currHeadCell = $hTable.find('tr:first-child')
//                        .find('th:nth-child(' + (cellIndex + 1) + ')');
//                colHeader = currHeadCell.text();
//                currData[rowIndex]['GGM'][colHeader]['GiorniTarget'] = $('#GiorniTarget').val();
//                currData[rowIndex]['GGM'][colHeader]['GiorniEff'] = $('#GiorniEff').val();
//                currData[rowIndex]['GGM'][colHeader]['FerieEff'] = $('#FerieEff').val();
//                currData[rowIndex]['GGM'][colHeader]['PermEff'] = $('#PermEff').val();
//                currData[rowIndex]['GGM'][colHeader]['MalEff'] = $('#MalEff').val();
//            }
//
//            function daysInMonth(Year, Month)
//            {
//                return new Date(Year, Month,
//                        0).getDate();
//            }
//

            function selectCell()
            {
                if (y > max.y - 2)
                    y = max.y - 2;
                if (x > max.x)
                    x = max.x;
                if (y < 2)
                    y = 2;
                if (x < 6)
                    x = 6;
                currentCell = $hTable.find('tr:nth-child(' + (y) + ')')
                        .find('td:nth-child(' + (x) + ')');
                content = currentCell.html();
                currentCell.toggleClass('selected');
                displayCellData();
                return currentCell;
            }

//            {class: "MyClass", type: "number", min: "0", max: "744", step: "1", size: "4", maxlenght: "3"})
            function edit(currentElement)
            {
                var input = $('<input>',
                {class: "MyClass", type: "text", size: "4", maxlenght: "3"})
                        .val(currentElement.html())
                currentElement.html(input)
                input.focus();
            }

            $hTable.find('td').click(function ()
            {
                clearCell();
                x = ($hTable.find('td').index(this) % (max.x));
                y = ($hTable.find('tr').index($(this).parent()) + 1);
                edit(selectCell());
            });

            $(document).keydown(function (e)
            {
                if (e.keyCode == 13)
                {
                    clearCell();
                    edit(selectCell());
                } else if (e.keyCode >= 37 && e.keyCode <= 40)
                {
                    getCellData();
                    clearCell();
                    switch (e.keyCode)
                    {
                        case 37:
                            x--;
                            break;
                        case 38:
                            y--;
                            break;
                        case 39:
                            x++;
                            break;
                        case 40:
                            y++;
                            break;
                    }
                    selectCell();
                    return false;
                }
            });
        });
    };
})(jQuery);
