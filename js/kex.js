jQuery(function ($){

    var $userEmail = $('#userEmail');
    var downTime = {};
    var prevPressedTime = 0;

    var prevKey = 0;
    var currentKey = 0;


    var holdsArray = {};
    var betweenArray = {};

    var sequenceHoldArray = [];
    var sequenceBetweenArray = [];

    var codeArray = [];

    var timeoutSend; // to set timeout for sending ajax request

    var _to_ascii = {
        '188': '44',
        '109': '45',
        '190': '46',
        '191': '47',
        '192': '96',
        '220': '92',
        '222': '39',
        '221': '93',
        '219': '91',
        '173': '45',
        '187': '61', //IE Key codes
        '186': '59', //IE Key codes
        '189': '45'  //IE Key codes
    };

    var shiftUps = {
        "96": "~",
        "49": "!",
        "50": "@",
        "51": "#",
        "52": "$",
        "53": "%",
        "54": "^",
        "55": "&",
        "56": "*",
        "57": "(",
        "48": ")",
        "45": "_",
        "61": "+",
        "91": "{",
        "93": "}",
        "92": "|",
        "59": ":",
        "39": "\"",
        "44": "<",
        "46": ">",
        "47": "?"
    };


    function getAsciiCode(pressed, withShift){
        withShift = !!withShift;
        if( _to_ascii.hasOwnProperty(pressed) ){
            pressed = _to_ascii[pressed];
        }

        if( !withShift && (pressed >= 65 && pressed <= 90) ){
            pressed = String.fromCharCode(pressed + 32);
        } else
            if( withShift && shiftUps.hasOwnProperty(pressed) ){
                //get shifted keyCode value
                pressed = shiftUps[pressed];
            } else{
                pressed = String.fromCharCode(pressed);
            }

        return pressed.charCodeAt(0);
    }

    function validateEmail(email){
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    function sendEmail(email, sequenceBetweenArray, sequenceHoldArray, codeArray, betweenArray, holdsArray) {
        if (validateEmail(email)) {
            // exclude first senseless element from sequenceBetween
            sequenceBetweenArray.shift();

            var timeArrays = {
                between: betweenArray,
                hold: holdsArray,
                sequenceBetween: sequenceBetweenArray,
                sequenceHold: sequenceHoldArray,
                codeArray: codeArray,
                text: email
            };

            $.post($('#loginForm').attr('action'), {timeArrays: JSON.stringify(timeArrays)}, function (data){
                $('#result').html(data);
            });
        }
    }

    $userEmail.keydown(function (e){
        var code = getAsciiCode(e.which, e.shiftKey);
        console.log('code pressed', code);
        if (code == 16) {
            return; //don't count shift presses
        } else if (code == 13) {
            var email = $(this).val();
            sendEmail(email, sequenceBetweenArray, sequenceHoldArray, codeArray, betweenArray, holdsArray);
            return;
        }

        var pressedTime = new Date().getTime();
        downTime[code] = pressedTime;

        //adding time between keys pressing
        if( !betweenArray[prevKey] ){
            betweenArray[prevKey] = {};
        }
        if( !betweenArray[prevKey][code] ){
            betweenArray[prevKey][code] = [];
        }
        betweenArray[prevKey][code].push(pressedTime - prevPressedTime);

        // interval is difference in time between two keys being pressed down
        var interval = pressedTime - prevPressedTime;
        sequenceBetweenArray.push(interval);

        // pass the code of key pressed
        codeArray.push(code);

        prevPressedTime = pressedTime;
        prevKey = code; //after time counting we can consider current key as previous


    }).keyup(function (e){
        clearTimeout(timeoutSend); // reset timeout everytime key is pressed

        var code = getAsciiCode(e.which, e.shiftKey);
        if (code == 16 || code == 13) {
            return; //don't count shift presses
        }
        currentKey = code;

        //adding time of key holding
        var holdTime = (new Date().getTime() - downTime[code]);
        if( !holdsArray[code] ){
            holdsArray[code] = [];
        }
        holdsArray[code].push(holdTime);

        sequenceHoldArray.push(holdTime);

        var email = $(this).val();
        timeoutSend = setTimeout(function() {
            sendEmail(email, sequenceBetweenArray, sequenceHoldArray, codeArray, betweenArray, holdsArray);
        }, 2000);
    });

});
