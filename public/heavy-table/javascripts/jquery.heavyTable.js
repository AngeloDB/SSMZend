(function ($)
    {
    var globalVar = 0;
    var myFunct;

    $.fn.heavyTable = function (params)
        {
        params = $.extend({
            startPosition: {
                x: 6,
                y: 0
            }
        }, params);

        var settings = jQuery.extend({
            param: 'value'
        }, params);
        //Define a reference to our function myplugin which it's 
        //part of jQuery namespace functions, so we can use later
        //within inside functions
        var $jquery = this;
        var isEdit = false;

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
            selectCell();

            function clearCell()
                {
                clearEvidCell();
                isEdit = false;
                var currCell = $hTable.find('.selected');
                content = $hTable.find('.selected input').val();
                if (typeof (content) !== 'undefined')
                    {
                    content = normalizeCell(content);
                    currCell.html(content);
                    if (content === '')
                        {
                        currCell.removeClass('gridEvid');
                        currCell.addClass('gridZero');
                        }
                    else
                        {
                        currCell.removeClass('gridZero');
                        currCell.addClass('gridEvid');
                        }
                    calcTotal();
                    }
                currCell.toggleClass('selected');
                }

            function normalizeCell(content)
                {
                if (isNaN(parseInt(content, 10)))
                    {
                    return '';
                    }
                content = parseInt(content, 10) || 0;
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


            function dispTarget()
                {
                }

            function dispEff()
                {
                }


            function selectCell()
                {
                if (y > max.y)
                    y = max.y;
                if (x > max.x)
                    x = max.x;
                if (y < 1)
                    y = 1;
//                if (x < 6)
//                    x = 6;
                currentCell = $hTable.find('tr:nth-child(' + (y) + ')')
                        .find('td:nth-child(' + (x) + ')');

                showTdPos(currentCell[0]);
                content = currentCell.html();
//                if(currentCell.is(".gridZero") || currentCell.is(".gridEvid"))
//                    {
                if (x >= 6 || (currentCell.is('[class^=areaBanner]') && x >= 1))
                    {
                    if (currentCell.is('[class^=areaBanner]'))
                        {
                        emptyCell = $hTable.find('tr:nth-child(' + (y) + ')')
                                .find('td:nth-child(' + (x - 4) + ')');
                        emptyCell.toggleClass('selected');
                        }
                    else
                        {
                        currentCell.toggleClass('selected');
                        }
                    displayCellData();
                    }
                return currentCell;
                }

//            {class: "MyClass", type: "number", min: "0", max: "744", step: "1", size: "4", maxlenght: "3"})
            function edit(currentElement)
                {
                if ((currentElement.is(".gridZero") || currentElement.is(".gridEvid")) && !isReadOnly())
                    {
//                    $('#spreadSheet').tooltip('close');    
                    evidCell();
                    var input = $('<input>',
                        {id: "heavyCellInput", 
                         class: "heavyCellInput", 
                         type: "text", 
                         size: "4", 
                         maxlenght: "3", 
                         title: 'Press "D" for daily detail, "N" for note'}).val(
                             currentElement.html());
                    currentElement.html(input);
                    input.focus();
                    input.select();
                    $(input).keypress(function(event){
                            if(event.which == 68 || event.which == 100){
                                openDetail(currentElement);
                                return false;
                            }
                            if(event.which == 78 || event.which == 110){
                                openNote(currentElement);
                                return false;
                            }
                        return true;    
                        });
                    isEdit = true;
                    }
                }

            $hTable.find('td').click(function ()
                {
                clearCell();
//                x = ($hTable.find('td').index($(this)) % (max.x));
//
//  if this.hasClass(dettaglio) openDetail() 
//
                x = (this.cellIndex + 1) % (max.x);
                y = ($hTable.find('tr').index($(this).parent()) + 1);
                var currentElement=selectCell();

                if(currentElement.is(".gridDetail") && 
                       (currentElement.is(".gridZero") || 
                        currentElement.is(".gridEvid") ||
                        currentElement.is(".gridLockedDis") ||
                        currentElement.is(".gridLocked")) && !isReadOnly())
                    {
                    openDetail(currentElement);
                    return false;
                    }
                else
                    {
                    edit(currentElement);
//                    return false;
                    }
//                edit(selectCell());
                });

            $(document).keydown(function (e)
                {
                if ($('.detailSheet').css('visibility') == 'visible' || $(".ui-dialog").is(":visible"))
                    {
                    return;
                    }
                if (!$hTable.find('.selected').children().first().is('input') && (
                        (e.keyCode === 13) || 
                        (e.keyCode == 68) ||
                        (e.keyCode >= 48 && e.keyCode <= 57) ||
                        (e.keyCode >= 96 && e.keyCode <= 105)))
                    {
                    clearCell();
                    var currentElement=selectCell();
                    if(currentElement.is(".gridDetail") && 
                            (currentElement.is(".gridZero") || 
                            currentElement.is(".gridLocked") || 
                            currentElement.is(".gridLockedDis") || 
                            currentElement.is(".gridEvid")) && !isReadOnly())
                        {
                        openDetail(currentElement);
                        return false;
                        }
                    else
                        {
                        edit(currentElement);
//                        return false;
                        }
                    }
//                else if (!$hTable.find('.selected').children().first().is('input') && (e.keyCode == 68)) // D
//                    {
//                    clearCell();
//                    var currentElement = selectCell();
//                    if ((currentElement.is(".gridZero") || currentElement.is(".gridEvid")) && !isReadOnly())
//                        {
//                        openDetail(currentElement);
//                        }
//                    }
                else if (!$hTable.find('.selected').children().first().is('input') && (e.keyCode == 78)) // N
                    {
                    clearCell();
                    var currentElement = selectCell();
                    if ((currentElement.is(".gridZero") || currentElement.is(".gridEvid")) && !isReadOnly())
                        {
                        openNote(currentElement);
                        }
                    }
                else if (e.keyCode >= 37 && e.keyCode <= 40)
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

    $.fn.heavyTable.test1 = function (newFunct)
        {
        myFunct = newFunct;
        };

    $.fn.heavyTable.test2 = function (params)
        {
        (myFunct)();
        };

})(jQuery);
