import $ from 'jquery';
$(document).ready(
    function (){
        const $itemContainer = $("#invoice_items");
        const template = $itemContainer.data("prototype");

        $("#btAddItem").on('click',function (){
            const itemNumber = 1 + $itemContainer.children().length;
            let html = template.replaceAll("__name__label__","Ligne "+itemNumber);
            html = html.replaceAll("__name_",itemNumber);

            $itemContainer.append(html);
        })

        $itemContainer.delegate('.delete','click',function () {
            $(this).parent().parent().parent().remove()
            $itemContainer.find("input[id$='_label']").attr("name", function (index) {
                return "invoice[items][" + index + "][label]";

            })
            $itemContainer.find("input[id$='_unitPrice']").attr("name", function (index) {
                return "invoice[items][" + index + "][unitPrice]";

            })
            $itemContainer.find("input[id$='_qt']").attr("name", function (index) {
                return "invoice[items][" + index + "][qt]";


            })
        })
    })