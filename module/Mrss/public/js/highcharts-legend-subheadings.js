// Plugin that will be moved to an external file once it's working

(function (Highcharts) {
    //var addEvent = H.addEvent;
    var H = Highcharts
    var firstHeadingPlaced = false

    H.Legend.prototype.renderLegendSubheading = function(item, legend)
    {

        var UNDEFINED,
            doc = window.document,
            math = Math,
            mathRound = math.round,
            mathFloor = math.floor,
            mathCeil = math.ceil,
            mathMax = math.max,
            mathMin = math.min,
            mathAbs = math.abs,
            mathCos = math.cos,
            mathSin = math.sin,
            mathPI = math.PI,
            deg2rad = mathPI * 2 / 360


        var //legend = this,
            chart = legend.chart,
            renderer = chart.renderer,
            options = legend.options,
            horizontal = options.layout === 'horizontal',
            symbolWidth = legend.symbolWidth,
            symbolPadding = options.symbolPadding,
            itemStyle = legend.itemStyle,
            itemHiddenStyle = legend.itemHiddenStyle,
            padding = legend.padding,
            itemDistance = horizontal ? H.pick(options.itemDistance, 20) : 0,
            ltr = !options.rtl,
            itemHeight,
            widthOption = options.width,
            itemMarginBottom = options.itemMarginBottom || 0,
            itemMarginTop = legend.itemMarginTop,
            initialItemX = legend.initialItemX,
            bBox,
            itemWidth,
            li = item.legendItem,
            useHTML = true


        var headingMarginTop = 20
        var headingMarginBottom = 0

        if (!firstHeadingPlaced) {
            headingMarginTop = 0
        }

        firstHeadingPlaced = true

        if (!li) { // generate it once, later move it

            // Generate the group box
            // A group to hold the symbol and text. Text is to be appended in Legend class.
            item.legendGroup = renderer.g('legend-item')
                .attr({ zIndex: 1 })
                .add(legend.scrollGroup);


            symbolWidth = 0
            var y = legend.baseline || 0
            y = y + headingMarginTop

            // Generate the list item text and add it to the group
            item.legendItem = li = renderer.text(
                '',
                ltr ? symbolWidth + symbolPadding : -symbolPadding,
                y,
                useHTML
                )
                .css(H.merge(item.visible ? itemStyle : itemHiddenStyle)) // merge to prevent modifying original (#1021)
                .attr({
                    align: ltr ? 'left' : 'right',
                    zIndex: 2
                })
                .add(item.legendGroup);

            // Get the baseline for the first item - the font size is equal for all
            if (!legend.baseline) {
                legend.fontMetrics = renderer.fontMetrics(itemStyle.fontSize, li);
                legend.baseline = legend.fontMetrics.f + 3 + itemMarginTop;
                li.attr('y', legend.baseline);
            }


        }

        // Colorize the items
        item.visible = true
        legend.colorizeItem(item, item.visible);

        // Always update the text
        //legend.setText(item);

        item.legendItem.attr({
            text: item.heading
        });

        // calculate the positions for the next line
        bBox = li.getBBox();

        itemWidth = item.checkboxOffset =
            options.itemWidth ||
            item.legendItemWidth ||
            symbolWidth + symbolPadding + bBox.width + itemDistance;
        legend.itemHeight = itemHeight = mathRound(item.legendItemHeight || bBox.height);

        // if the item exceeds the width, start a new line
        if (horizontal && legend.itemX - initialItemX + itemWidth >
            (widthOption || (chart.chartWidth - 2 * padding - initialItemX - options.x))) {
            legend.itemX = initialItemX;
            legend.itemY += headingMarginTop + legend.lastLineHeight + headingMarginBottom;
            legend.lastLineHeight = 0; // reset for next line (#915, #3976)
        }


        // Set the edge positions
        legend.maxItemWidth = mathMax(legend.maxItemWidth, itemWidth);
        legend.lastItemY = headingMarginTop + legend.itemY + headingMarginBottom;
        legend.lastLineHeight = mathMax(itemHeight, legend.lastLineHeight); // #915

        // cache the position of the newly generated or reordered items
        item._legendItemPos = [legend.itemX, legend.itemY];

        // advance
        if (horizontal) {
            legend.itemX += itemWidth;

        } else {
            legend.itemY += headingMarginTop + itemHeight + headingMarginBottom;
            legend.lastLineHeight = itemHeight;
        }

        // the width of the widest item
        legend.offsetWidth = widthOption || mathMax(
                (horizontal ? legend.itemX - initialItemX - itemDistance : itemWidth) + padding,
                legend.offsetWidth
            );
    }



    H.wrap(H.Legend.prototype, 'getAllItems', function (proceed) {

        var results = proceed.call(this)


        var currentHeading = null
        var headingsToAdd = []
        H.each(results, function(item, i) {
            //.log(item.name)
            var nameString = String(item.name);
            var nameParts = nameString.split('|')
            if (nameParts[1]) {
                if (currentHeading == nameParts[1]) {

                } else {
                    currentHeading = nameParts[1]
                    headingsToAdd.unshift({index: i, heading: nameParts[1]})
                }

            }

            item.name = nameParts[0]
            //console.log(nameParts)
        })

        H.each(headingsToAdd, function(headingInfo) {
            var heading = {
                heading: headingInfo.heading
            }
            results.splice(headingInfo.index, 0, heading)
        })

        return results
    })



    H.wrap(H.Legend.prototype, 'renderItem', function (proceed, item) {

        if (item.heading) {
            this.renderLegendSubheading(item, this)
        } else {
            //this.renderLegendSubheading(item, this)
            proceed.call(this, item)
        }

    })
}(Highcharts));

