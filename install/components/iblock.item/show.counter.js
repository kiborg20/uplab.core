document.addEventListener('DOMContentLoaded', function () {

    console.log('qwerty!');

    window.upIblockItemCounter = {};

    // noinspection JSUnresolvedFunction
    document.querySelectorAll('[data-up-iblock-item-counter]').forEach(function (item, i) {

        var id = item.dataset.upIblockItemCounter || false;
        if (!id) return;

        if (window.upIblockItemCounter[id]) {
            item.innerText = window.upIblockItemCounter[id];
            return;
        }

        var idList = id.split(':');
        var iblockId = idList[0] || '';
        var elementId = idList[1] || '';

        console.log({ idList, iblockId, elementId });

        if (
            BX &&
            BX.hasOwnProperty('ajax') &&
            BX.ajax &&
            BX.ajax.hasOwnProperty('runComponentAction') &&
            BX.ajax.runComponentAction
        ) {

            var request = BX.ajax.runComponentAction(
                'uplab.core:iblock.item',
                'getCounter',
                {
                    mode: 'class',
                    data: {
                        iblockId,
                        elementId,
                    }
                }
            );
            request.then(function (response) {
                console.log(response);
                var counter = response.data.counter || '';
                if (counter) {
                    window.upIblockItemCounter[id] = counter;
                    item.innerText = counter;
                }
            });

        }
    });

});