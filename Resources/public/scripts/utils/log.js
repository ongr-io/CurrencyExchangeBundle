(function(context){

    var debug = true;

    context.log = debug ? function(msg)
    {
        console && console.log(msg);
    } : function(){};

})(window);
