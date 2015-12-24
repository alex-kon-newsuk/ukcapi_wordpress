var xpath = require('xpath');
var dom = require('xmldom').DOMParser;
var XMLSerializer = require('xmldom').XMLSerializer;

module.exports = {

    identity: function(id, cb)
    {
        return id;
    },

    createUUID: function ()
    {
        var s = [];
        var hexDigits = "0123456789abcdef";
        for (var i = 0; i < 36; i++) {
            s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
        }
        s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
        s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
        s[8] = s[13] = s[18] = s[23] = "-";

        var uuid = s.join("");
        return uuid;
    },

    escapeXmlString: function(string)
    {
        if (string === null || string === undefined) return;
        return string.replace(/([&"<>'])/g, function(str, item)
        {
            return {
                '>': '&gt;'
                , '<': '&lt;'
                , "'": '&apos;'
                , '"': '&quot;'
                , '&': '&amp;'
            }[item];
        });
    },

    extractPathValueFromXml: function (xmlDoc, namespaces, mappings)
    {
        var result = {};
        for (var key in mappings)
        {
            console.log('query for xpath: ', mappings[key]);

            try
            {
                var select = xpath.useNamespaces(namespaces);
                var x = select(mappings[key], xmlDoc);

                //var x = xpath.select(mappings[key], xmlDoc);
                if (x) {
                    result[key] = x.toString();
                }
            }
            catch(e)
            {

            }
        }
        return result;
    }

};
