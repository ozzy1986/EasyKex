jQuery(function ($){

    // focus on input first things first
    $('#userEmail').focus();

    // forbid pasting to input
    $('#userEmail').on('paste drop', function() {
        return false;
    });
    // forbid backspace, delete, arrow left and arrow right
    $('#userEmail').on('keydown', function(e) {
        var evt = e || window.event;
        if (evt) {
            var keyCode = evt.charCode || evt.keyCode;
            if (keyCode === 8 || keyCode === 37 || keyCode === 38 || keyCode === 39 || keyCode === 40 || keyCode === 46) {
                if (evt.preventDefault) {
                    evt.preventDefault();
                } else {
                    evt.returnValue = false;
                }
            }
        }
    });
    // forbid putting cursor in the middle or beginning of text
    $('#userEmail').on('click focus', function() {
        var val = $(this).val(); // store the value of the element
        $(this).val('').val(val); // clear the value of the element and set that value back.
    });




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

    var totalTime = 0;
    var startTime = 0;

    var keyIsPressed = false;
    var overlapTime = [];
    var overlapTimeTemp = 0;

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


    function clearAll(clearResult) {
        if (typeof clearResult === 'undefined') {
            clearResult = true;
        }
        downTime = {};
        prevPressedTime = 0;
        prevKey = 0;
        currentKey = 0;
        holdsArray = {};
        betweenArray = {};
        sequenceHoldArray = [];
        sequenceBetweenArray = [];
        codeArray = [];

        $('#userEmail').val('').focus();
        if (clearResult) {
            $('#result').html('');
        }
    }

    $('#clearButton').click(function() {
        clearAll();
    });


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

    function sendEmail(email, sequenceBetweenArray, sequenceHoldArray, codeArray, betweenArray, holdsArray, totalTime, overlapTime) {
        if (validateEmail(email)) {
            // exclude first senseless element from sequenceBetween
            sequenceBetweenArray.shift();
            delete betweenArray[0];

            var timeArrays = {
                sequenceBetween: sequenceBetweenArray,
                sequenceHold: sequenceHoldArray,
                codeArray: codeArray,
                text: email,
                between: betweenArray,
                hold: holdsArray,
                totalTime: totalTime,
                overlapTime: overlapTime
            };

            $.post($('#loginForm').attr('action'), {timeArrays: JSON.stringify(timeArrays)}, function (data){
                $('#result').html(data);
                if (data.substring(0, 12) == '<br>New user') {
                    // if it was new user then lets clear input
                    clearAll(false);
                }
            });
        }
    }

    $userEmail.keydown(function (e){
        var code = getAsciiCode(e.which, e.shiftKey);
        if (code == 16) {
            return; //don't count shift presses
        } else if (code == 13) {
            var email = $(this).val();
            sendEmail(email, sequenceBetweenArray, sequenceHoldArray, codeArray, betweenArray, holdsArray, totalTime, overlapTime);
            return;
        }

        if (keyIsPressed) {
            overlapTimeTemp = new Date().getTime();
        }
        keyIsPressed = true;

        if (prevKey == 0) {
            // if first time key is pressed then start countdown
            startTime = new Date().getTime();
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
        if (code == 16) {
            return; //don't count shift presses
        } else if (code == 13) {
            totalTime = (new Date().getTime() - startTime);
            return;
        }
        totalTime = (new Date().getTime() - startTime);

        if (overlapTimeTemp > 0) {
            overlapTime.push((new Date().getTime() - overlapTimeTemp));
        }
        keyIsPressed = false;
        overlapTimeTemp = 0;

        currentKey = code;

        //adding time of key holding
        var holdTime = (new Date().getTime() - downTime[code]);
        if (holdTime) {
            if (!holdsArray[code]) {
                holdsArray[code] = [];
            }
            holdsArray[code].push(holdTime);

            sequenceHoldArray.push(holdTime);
        }

        var email = $(this).val();
        timeoutSend = setTimeout(function() {
            sendEmail(email, sequenceBetweenArray, sequenceHoldArray, codeArray, betweenArray, holdsArray, totalTime, overlapTime);
        }, 2000);
    });

});
