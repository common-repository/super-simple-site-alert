function sssa_timestampToNumberedDateTimeUniversal(timestamp, stamp)
{
    if(typeof stamp == "undefined"){
        stamp = "Y/m/d - h:i a";
    }

    if(timestamp != ""){
        var t = new Date(timestamp * 1000);
        var formatted = t.format(stamp);
        return formatted;
    }else{
        return "";
    }
}

function sssa_timestampToNumberedDateTime(timestamp, stamp)
{
    if(typeof stamp == "undefined"){
        stamp = "m/d/Y - h:i a";
    }

    if(timestamp != ""){
        var t = new Date(timestamp * 1000);
        var formatted = t.format(stamp);
        return formatted;
    }else{
        return "";
    }
}

function sssa_getHost()
{
	return window.location.host;
}

function sssa_insertParam(key, value) {
    var uri = window.location.href;
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        var newUrl = uri.replace(re, '$1' + key + "=" + value + '$2');
    }else {
        var newUrl = uri + separator + key + "=" + value;
    }

    //You can reload the url like so
     window.history.pushState("", "Page Title Here", newUrl);
}

function sssa_removeFromArray(array, val) {
    const index = array.indexOf(val);
    if (index > -1) {
      array.splice(index, 1);
    }

    return array;
}

function sssa_trim (s, c) {
  if (c === "]") c = "\\]";
  if (c === "^") c = "\\^";
  if (c === "\\") c = "\\\\";
  return s.replace(new RegExp(
    "^[" + c + "]+|[" + c + "]+$", "g"
  ), "");
}
