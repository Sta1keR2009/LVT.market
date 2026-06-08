/**
 * Created by Mebiys on 04.06.2017.
 */

function catapulto_delivery_wndController(params) {
    var self = this;

    var defaultParams = {
        title: "",
        content: "",
        icon: "head-block",
        resizable: true,
        draggable: true,
        height: '500',
        width: '500',
        buttons: []
    };

    var wndLink = false;

    init();

    // private
    function init(params){
        if(typeof(params) === 'undefined')
            params = defaultParams;

        var obSetups = {};
        for(var i in defaultParams)
            obSetups[i] = getOpt(i);

        wndLink = new BX.CDialog(obSetups);
    }

    // service
    function checkOpt(wat){
        return (typeof(params[wat]) !== 'undefined');
    }

    function getOpt(wat){
        return (checkOpt(wat)) ? params[wat] : defaultParams[wat];
    }

    function checkReady(action) {
        if(self.getWnd())
            return true;
        else{
            var title = (getOpt('title')) ? 'of title "'+getOpt('title')+'" ' : '';
            action    = (typeof(action) === 'undefined') ? '' : ' for '+action;
            console.warn('Window object '+title+'is not ready'+action);
            return false;
        }
    }

    // public
    this.getWnd = function(){
        return wndLink;
    };

    this.open   = function(){
        if(checkReady('open'))
            self.getWnd().Show();
    };

    this.setContent = function(content){
        if(checkReady('adding content'))
            self.getWnd().SetContent(content);
    };

    this.close  = function(){
        if(checkReady('close'))
            self.getWnd().Close();
    };
}