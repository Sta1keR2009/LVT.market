// selectOffer js
function useOfferSelect() {
    BX.ready(() => {
        if (!("SelectOfferProp" in window)) {
            const $catalogItems = $(".catalog-items");
            if ($catalogItems.length) {
                $catalogItems.iAppear(
                    () => { BX.loadExt('aspro_select_offer') },
                    { accX: 0, accY: 100 }
                );
            }
        }
    });
}