$(document).ready(function() {
    let t = setInterval(function() {        
        if (typeof map === "object" && map !== null) {
            clearInterval(t);
            
            setTimeout(function(){
                if (typeof clusterer === "object" && clusterer !== null && "setBounds" in map && "getBounds" in clusterer) {
                    map.setBounds(clusterer.getBounds(), {
                    zoomMargin: 40,
                    // checkZoomRange: true
                });
                } else if (typeof bounds === "object" && bounds !== null && "fitBounds" in map && "getCenter" in bounds) {
                    map.fitBounds(bounds);
                }
            }, 100);
        }
    }, 100);

    $('.stores-list__item').click(function(){
        let index = $(this).index();
        let $detail = $(this).closest('.stores-list').find('.stores-list__detail').eq(index);
    
        if ($detail.length) {
            $detail.addClass('current').siblings().removeClass('current');
            $(this).closest('.stores-list__items__wrapper').hide();
            $($detail).closest('.stores-list__details__wrapper').show();
        }
    });
    
    $('.stores-list__detail-close').click(function(){
        let $detail = $(this).closest('.stores-list__detail');
    
        $($detail).closest('.stores-list__details__wrapper').hide();
        $(this).closest('.stores-list').find('.stores-list__items__wrapper').show();
    
        $detail.removeClass('current');

        if (typeof map === "object" && map !== null) {
			if (typeof clusterer === "object" && clusterer !== null && "setBounds" in map && "getBounds" in clusterer) {
				map.setBounds(clusterer.getBounds(), {
				zoomMargin: 40,
				// checkZoomRange: true
			});
			} else if (typeof bounds === "object" && bounds !== null && "fitBounds" in map && "getCenter" in bounds) {
				map.fitBounds(bounds);
			}
		}
    });

    $('.stores-list__item-title').click(function(e) {
        e.preventDefault();
    });
});