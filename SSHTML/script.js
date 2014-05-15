window.onload = function (){

    var destinationUrl = "http://easykex.aladevelop.com/SSHTML/template.php";


    function createCORSRequest(method, url){
        var xhr = new XMLHttpRequest();
        if( "withCredentials" in xhr ){
            // Check if the XMLHttpRequest object has a "withCredentials" property.
            // "withCredentials" only exists on XMLHTTPRequest2 objects.
            xhr.open(method, url, true);
        } else
            if( typeof XDomainRequest != "undefined" ){
                // Otherwise, check if XDomainRequest.
                // XDomainRequest only exists in IE, and is IE's way of making CORS requests.
                xhr = new XDomainRequest();
                xhr.open(method, url);
            } else{
                // Otherwise, CORS is not supported by the browser.
                xhr = null;
            }
        return xhr;
    }


    var getParams = window.location.search.replace("?", "");
    var xhr = createCORSRequest('GET', destinationUrl + "?" + getParams);
    if( !xhr ){
        throw new Error('CORS not supported');
    }

    xhr.onload = afterLoadScript;
    xhr.onerror = function (){
        console.log('There was an error!');
    };
    xhr.send();


    function afterLoadScript(){
        var $wrap = document.getElementsByClassName('wholeAdWrapper451')[0];
        $wrap.innerHTML = xhr.response;

        var timer;
        var $vid = $wrap.getElementsByTagName('video')[0];
        clearTimeout(timer);
        if( $vid ){
            $vid.load();
            $vid.play();
            timer = setTimeout(function (){
                $vid.pause();
                $vid.currentTime = 0;
            }, 30000);
        }
    }
};
