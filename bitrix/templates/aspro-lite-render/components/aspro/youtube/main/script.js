$(document).ready(function () {

    if (!window["YoutubePlayerScriptLoaded"]) {
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        window["YoutubePlayerScriptLoaded"] = true;
    }

    $(".youtube-list__item-preview").on("click", function(){
        var _this = $(this);
        _this.addClass('videoloaded');

        var player;
        player = new YT.Player("youtube-player-id-" + _this.data("video-id"), {
            videoId: _this.data("video-id"),
            events: {
            'onReady': onPlayerReady,
            }
        });
        
        function onPlayerReady(event) {
            event.target.playVideo();
        }
    });
});