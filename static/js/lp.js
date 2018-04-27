"use strict";
var __read = (this && this.__read) || function (o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
};
var __spread = (this && this.__spread) || function () {
    for (var ar = [], i = 0; i < arguments.length; i++) ar = ar.concat(__read(arguments[i]));
    return ar;
};
var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var DeferredPromise = /** @class */ (function () {
    function DeferredPromise() {
        var _this = this;
        this.resolve = function () { };
        this.reject = function () { };
        this._promise = new Promise(function (resolve, reject) {
            // assign the resolve and reject functions to `this`
            // making them usable on the class instance
            _this.resolve = resolve;
            _this.reject = reject;
        });
        // bind `then` and `catch` to implement the same interface as Promise
        this.then = this._promise.then.bind(this._promise);
        this.catch = this._promise.catch.bind(this._promise);
        this[Symbol.toStringTag] = 'Promise';
    }
    DeferredPromise.prototype.promise = function () {
        return this._promise;
    };
    return DeferredPromise;
}());
if (!Promise.prototype.done) {
    Promise.prototype.done = function (onFulfilled, onRejected) {
        this.then(onFulfilled, onRejected)
            .catch(function (reason) {
            // 抛出一个全局错误
            setTimeout(function () { throw reason; }, 0);
        });
    };
}
if (!Promise.prototype.finally) {
    Promise.prototype.finally = function (callback) {
        var P = this.constructor;
        return this.then(function (value) { return P.resolve(callback()).then(function () { return value; }); }, function (reason) { return P.resolve(callback()).then(function () { throw reason; }); });
    };
}
function promiseWrap(callback) {
    var args = [];
    for (var _i = 1; _i < arguments.length; _i++) {
        args[_i - 1] = arguments[_i];
    }
    return Promise.resolve(callback.call.apply(callback, __spread([this], args)));
}
/**
 * only one parameter
 * @param args
 */
function promiseReject(args) {
    return Promise.reject(args);
}
/// <reference path="polyfill/deferredPromise.ts" />
var LP;
/// <reference path="polyfill/deferredPromise.ts" />
(function (LP) {
    //base uri
    var thiscript = window.document.currentScript;
    if (!thiscript) {
        var scripts = window.document.getElementsByTagName("script");
        thiscript = scripts[scripts.length - 1];
    }
    var src = thiscript['src'] ? thiscript['src'] : thiscript.getAttribute('src');
    var _baseuri = src.toString().match(/[^\/:](\/.*)static\/js\/lp(\.min)?\.js/i) ? src.toString().match(/[^\/:](\/.*)static\/js\/lp(\.min)?\.js/i)[1] : src.toString().replace(/\\/g, '/').replace(/\/[^\/]*\/?$/, '') + '/';
    if (!_baseuri)
        _baseuri = '/';
    LP.baseuri = _baseuri;
    if (jQuery)
        jQuery['baseuri'] = _baseuri;
})(LP || (LP = {}));
var LP;
(function (LP) {
    LP.QUERY_LANGUAGE = {
        'error': '错误',
        'reload': '重新载入',
        'redirect': '页面跳转',
        'unselected': '请至少选择一项！',
        'network_timeout': '网络故障，请检查网络连接后重试！',
        'parser_error': '数据解析失败，刷新重试下？',
        'server_error': '服务器可能出现了点问题，刷新页面重试下？',
        'encrypt_key': '数据已经加密，但未传递正确的公钥。',
        'encrypt_js': '数据已经加密，但页面未加载解密JS。',
        'encrypt_string': '数据已经加密，但密文解密失败，请联系管理员。',
        'encrypt_unserialize': '数据已经加密，但解密后反序列化失败，请联系管理员。',
    };
})(LP || (LP = {}));
var LP;
(function (LP) {
    var sec;
    (function (sec) {
        //ssl
        var RSAGenerator = /** @class */ (function () {
            function RSAGenerator(cacheDriver) {
                if (cacheDriver === void 0) { cacheDriver = window.sessionStorage; }
                this.rsa = this.getRSAKeys(cacheDriver);
            }
            RSAGenerator.prototype.getRSAKeys = function (cacheDriver) {
                var rsa_str = cacheDriver.getItem('l+rsa');
                var rsa = rsa_str ? JSON.parse(rsa_str) : null;
                if (!rsa) {
                    if (window['JSEncrypt']) {
                        var crypt = new window['JSEncrypt']({ default_key_size: 1024 });
                        var key = crypt.getKey();
                        rsa = {
                            private: key.getPrivateKey(),
                            public: key.getPublicKey(),
                        };
                        cacheDriver.setItem('l+rsa', JSON.stringify(rsa));
                    }
                    else {
                        rsa = {
                            public: '',
                            private: ''
                        };
                    }
                }
                return rsa;
            };
            RSAGenerator.prototype.encrypt = function (text) {
                if (!window['JSEncrypt'])
                    return text;
                var crypt = new window['JSEncrypt']();
                crypt.setKey(this.rsa.public);
                return crypt.encrypt(text);
            };
            RSAGenerator.prototype.decrypt = function (text) {
                if (!window['JSEncrypt'])
                    return text;
                var crypt = new window['JSEncrypt']();
                crypt.setKey(this.rsa.private);
                return crypt.decrypt(text);
            };
            return RSAGenerator;
        }());
        sec.RSAGenerator = RSAGenerator;
    })(sec = LP.sec || (LP.sec = {}));
})(LP || (LP = {}));
/**
 * 序列化
 * @param  {mixed} mixedValue Object/Array/String/...
 * @return {String}           序列化后的字符串
 */
function serialize(mixedValue) {
    //  discuss at: http://locutus.io/php/serialize/
    // original by: Arpad Ray (mailto:arpad@php.net)
    // improved by: Dino
    // improved by: Le Torbi (http://www.letorbi.de/)
    // improved by: Kevin van Zonneveld (http://kvz.io/)
    // bugfixed by: Andrej Pavlovic
    // bugfixed by: Garagoth
    // bugfixed by: Russell Walker (http://www.nbill.co.uk/)
    // bugfixed by: Jamie Beck (http://www.terabit.ca/)
    // bugfixed by: Kevin van Zonneveld (http://kvz.io/)
    // bugfixed by: Ben (http://benblume.co.uk/)
    // bugfixed by: Codestar (http://codestarlive.com/)
    //    input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)
    //    input by: Martin (http://www.erlenwiese.de/)
    //      note 1: We feel the main purpose of this function should be to ease
    //      note 1: the transport of data between php & js
    //      note 1: Aiming for PHP-compatibility, we have to translate objects to arrays
    //   example 1: serialize(['Kevin', 'van', 'Zonneveld'])
    //   returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
    //   example 2: serialize({firstName: 'Kevin', midName: 'van'})
    //   returns 2: 'a:2:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";}'
    var val, key, okey;
    var ktype = '';
    var vals = '';
    var count = 0;
    var _utf8Size = function (str) {
        var size = 0;
        var i = 0;
        var l = str.length;
        var code = 0;
        for (i = 0; i < l; i++) {
            code = str.charCodeAt(i);
            if (code < 0x0080) {
                size += 1;
            }
            else if (code < 0x0800) {
                size += 2;
            }
            else {
                size += 3;
            }
        }
        return size;
    };
    var _getType = function (inp) {
        var match;
        var key;
        var cons;
        var types;
        var type = typeof inp;
        if (type === 'object' && !inp) {
            return 'null';
        }
        if (type === 'object') {
            if (!inp.constructor) {
                return 'object';
            }
            cons = inp.constructor.toString();
            match = cons.match(/(\w+)\(/);
            if (match) {
                cons = match[1].toLowerCase();
            }
            types = ['boolean', 'number', 'string', 'array'];
            for (key in types) {
                if (cons === types[key]) {
                    type = types[key];
                    break;
                }
            }
        }
        return type;
    };
    var type = _getType(mixedValue);
    switch (type) {
        case 'function':
            val = '';
            break;
        case 'boolean':
            val = 'b:' + (mixedValue ? '1' : '0');
            break;
        case 'number':
            val = (Math.round(mixedValue) === mixedValue ? 'i' : 'd') + ':' + mixedValue;
            break;
        case 'string':
            val = 's:' + _utf8Size(mixedValue) + ':"' + mixedValue + '"';
            break;
        case 'array':
        case 'object':
            val = 'a';
            /*
            if (type === 'object') {
                let objname = mixedValue.constructor.toString().match(/(\w+)\(\)/);
                if (objname === undefined) {
                    return;
                }
                objname[1] = serialize(objname[1]);
                val = 'O' + objname[1].substring(1, objname[1].length - 1);
            }
            */
            for (key in mixedValue) {
                if (mixedValue.hasOwnProperty(key)) {
                    ktype = _getType(mixedValue[key]);
                    if (ktype === 'function') {
                        continue;
                    }
                    okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                    vals += serialize(okey) + serialize(mixedValue[key]);
                    count++;
                }
            }
            val += ':' + count + ':{' + vals + '}';
            break;
        //case 'undefined':
        default:
            // Fall-through
            // if the JS object has a property which contains a null value,
            // the string cannot be unserialized by PHP
            val = 'N';
            break;
    }
    if (type !== 'object' && type !== 'array') {
        val += ';';
    }
    return val;
}
/**
 * 反序列化
 * @param  {String} data
 * @return {mixed}      得到的Object/Array/String/...
 */
function unserialize(data) {
    //  discuss at: http://locutus.io/php/unserialize/
    // original by: Arpad Ray (mailto:arpad@php.net)
    // improved by: Pedro Tainha (http://www.pedrotainha.com)
    // improved by: Kevin van Zonneveld (http://kvz.io)
    // improved by: Kevin van Zonneveld (http://kvz.io)
    // improved by: Chris
    // improved by: James
    // improved by: Le Torbi
    // improved by: Eli Skeggs
    // bugfixed by: dptr1988
    // bugfixed by: Kevin van Zonneveld (http://kvz.io)
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //  revised by: d3x
    //    input by: Brett Zamir (http://brett-zamir.me)
    //    input by: Martin (http://www.erlenwiese.de/)
    //    input by: kilops
    //    input by: Jaroslaw Czarniak
    //      note 1: We feel the main purpose of this function should be
    //      note 1: to ease the transport of data between php & js
    //      note 1: Aiming for PHP-compatibility, we have to translate objects to arrays
    //   example 1: unserialize('a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}')
    //   returns 1: ['Kevin', 'van', 'Zonneveld']
    //   example 2: unserialize('a:2:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";}')
    //   returns 2: {firstName: 'Kevin', midName: 'van'}
    var utf8Overhead = function (chr) {
        // http://locutus.io/php/unserialize:571#comment_95906
        var code = chr.charCodeAt(0);
        var zeroCodes = [
            338,
            339,
            352,
            353,
            376,
            402,
            8211,
            8212,
            8216,
            8217,
            8218,
            8220,
            8221,
            8222,
            8224,
            8225,
            8226,
            8230,
            8240,
            8364,
            8482
        ];
        if (code < 0x0080 || code >= 0x00A0 && code <= 0x00FF || zeroCodes.indexOf(code) !== -1) {
            return 0;
        }
        if (code < 0x0800) {
            return 1;
        }
        return 2;
    };
    var error = function (type, msg, filename, line) {
        switch (type) {
            case 'SyntaxError':
                throw new SyntaxError(msg, filename, line);
            case 'Error':
            default:
                throw new Error(msg, filename, line);
        }
    };
    var readUntil = function (data, offset, stopchr) {
        var i = 2;
        var buf = [];
        var chr = data.slice(offset, offset + 1);
        while (chr !== stopchr) {
            if ((i + offset) > data.length) {
                error('Error', 'Invalid');
            }
            buf.push(chr);
            chr = data.slice(offset + (i - 1), offset + i);
            i += 1;
        }
        return [buf.length, buf.join('')];
    };
    var readChrs = function (data, offset, length) {
        var i, chr, buf;
        buf = [];
        for (i = 0; i < length; i++) {
            chr = data.slice(offset + (i - 1), offset + i);
            buf.push(chr);
            length -= utf8Overhead(chr);
        }
        return [buf.length, buf.join('')];
    };
    var _unserialize = function (data, offset) {
        var dtype;
        var dataoffset;
        var keyandchrs;
        var keys;
        var contig;
        var length;
        var array;
        var readdata;
        var readData;
        var ccount;
        var stringlength;
        var i;
        var key;
        var kprops;
        var kchrs;
        var vprops;
        var vchrs;
        var value;
        var chrs = 0;
        var typeconvert = function (x) {
            return x;
        };
        if (!offset) {
            offset = 0;
        }
        dtype = (data.slice(offset, offset + 1)).toLowerCase();
        dataoffset = offset + 2;
        switch (dtype) {
            case 'i':
                typeconvert = function (x) {
                    return parseInt(x, 10);
                };
                readData = readUntil(data, dataoffset, ';');
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 1;
                break;
            case 'b':
                typeconvert = function (x) {
                    return parseInt(x, 10) !== 0;
                };
                readData = readUntil(data, dataoffset, ';');
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 1;
                break;
            case 'd':
                typeconvert = function (x) {
                    return parseFloat(x);
                };
                readData = readUntil(data, dataoffset, ';');
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 1;
                break;
            case 'n':
                readdata = null;
                break;
            case 's':
                ccount = readUntil(data, dataoffset, ':');
                chrs = ccount[0];
                stringlength = ccount[1];
                dataoffset += chrs + 2;
                readData = readChrs(data, dataoffset + 1, parseInt(stringlength, 10));
                chrs = readData[0];
                readdata = readData[1];
                dataoffset += chrs + 2;
                if (chrs !== parseInt(stringlength, 10) && chrs !== readdata.length) {
                    error('SyntaxError', 'String length mismatch');
                }
                break;
            case 'a':
                readdata = {};
                keyandchrs = readUntil(data, dataoffset, ':');
                chrs = keyandchrs[0];
                keys = keyandchrs[1];
                dataoffset += chrs + 2;
                length = parseInt(keys, 10);
                contig = true;
                for (i = 0; i < length; i++) {
                    kprops = _unserialize(data, dataoffset);
                    kchrs = kprops[1];
                    key = kprops[2];
                    dataoffset += kchrs;
                    vprops = _unserialize(data, dataoffset);
                    vchrs = vprops[1];
                    value = vprops[2];
                    dataoffset += vchrs;
                    if (key !== i) {
                        contig = false;
                    }
                    readdata[key] = value;
                }
                if (contig) {
                    array = new Array(length);
                    for (i = 0; i < length; i++) {
                        array[i] = readdata[i];
                    }
                    readdata = array;
                }
                dataoffset += 1;
                break;
            default:
                error('SyntaxError', 'Unknown / Unhandled data type(s): ' + dtype);
                break;
        }
        return [dtype, dataoffset - offset, typeconvert(readdata)];
    };
    return _unserialize((data + ''), 0)[2];
}
/// <reference path="rsa.ts" />
/// <reference path="../polyfill/serialize.ts" />
var LP;
/// <reference path="rsa.ts" />
/// <reference path="../polyfill/serialize.ts" />
(function (LP) {
    var sec;
    (function (sec) {
        var ssl = new sec.RSAGenerator(window.sessionStorage);
        var Encryptor = /** @class */ (function () {
            function Encryptor() {
            }
            Encryptor.prototype.actived = function () {
                return typeof window['JSEncrypt'] != 'undefined' && !!window.sessionStorage;
            };
            Encryptor.prototype.getPublicKey = function () {
                return this.actived() ? ssl.rsa.public : '';
            };
            Encryptor.prototype.jsonError = function (content) {
                return { result: 'error', message: { title: LP.QUERY_LANGUAGE.error, content: content }, data: null };
            };
            Encryptor.prototype.decrypt = function (json) {
                if (typeof json != 'undefined'
                    && json instanceof Object
                    && typeof json.result != 'undefined'
                    && json.result == 'api'
                    && typeof json.encrypted == 'string') {
                    var key = void 0, encrypted_json = void 0, encrypted = void 0, base64js = window['base64js'], aesjs = window['aesjs'];
                    try {
                        key = ssl.decrypt(json.encrypted);
                    }
                    catch (e) {
                        console.log(e.stack);
                        return this.jsonError(LP.QUERY_LANGUAGE.encrypt_key + e.message);
                    }
                    encrypted = json.data;
                    try {
                        var s = base64js.toByteArray(encrypted);
                        encrypted_json = JSON.parse(aesjs.util.convertBytesToString(s)); //json_decode()
                    }
                    catch (e) {
                        console.log(e.stack);
                        return this.jsonError(LP.QUERY_LANGUAGE.encrypt_string + e.message);
                    }
                    try {
                        //base64 decode
                        var keyBytes = base64js.toByteArray(key), ivBytes = base64js.toByteArray(encrypted_json.iv), valueBytes = base64js.toByteArray(encrypted_json.value);
                        //aes cbc
                        var aesCbc = new aesjs.ModeOfOperation.cbc(keyBytes, ivBytes);
                        var decryptedBytes = aesCbc.decrypt(valueBytes);
                        var decypted = aesjs.util.convertBytesToString(decryptedBytes);
                        //unserialize
                        json.data = unserialize(decypted);
                    }
                    catch (e) {
                        console.log(e.stack);
                        return this.jsonError(LP.QUERY_LANGUAGE.encrypt_unserialize + e.message);
                    }
                }
                delete json.encrypted;
                return json;
            };
            return Encryptor;
        }());
        sec.Encryptor = Encryptor;
    })(sec = LP.sec || (LP.sec = {}));
})(LP || (LP = {}));
if (!String.prototype.noHTML) {
    /**
     * 删除所有HTML标签
     *
     * let str = "<a href=''>我爱你</a>".toPre();
     * 返回 '我爱你'
     *
     * @return {String}
     */
    String.prototype.noHTML = function () {
        return this.replace(/<script[^>]*?>.*?<\/script>/ig, '').replace(/<[\/\!]*?[^<>]*?>/g, '').replace(/<style[^>]*?>.*?<\/style>/ig, '').replace(/<![\s\S]*?--[ \t\n\r]*>/, '').replace(/([\r\n])[\s]+/, '').replace(/&(quot|#34|amp|#38|lt|#60|gt|#62|nbsp|#160)/i, '');
    };
}
if (!String.prototype.toHTML) {
    /**
     * 转义字符串的HTML字符，主要有 < > " ' &
     * let str = '<a href="xxx">'.toHTML();
     * 返回 '&lt;a href=&quot;xxx&quot;&gt;'
     *
     * @return {String}
     */
    String.prototype.toHTML = function () {
        return this.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    };
}
if (!String.prototype.toPre) {
    /**
     * 转义字符串的空格、回车、制表符，也就是将textarea输入的文本可以原样显示到屏幕
     * 类似于<pre>标签
     *
     * var str = " 空格\n第\t二行".toPre();
     * 返回 '&nbsp;空格<br />第&nbsp;&nbsp;&nbsp;&nbsp;二行'
     *
     * @return {String}
     */
    String.prototype.toPre = function () {
        return this.replace(/\040/g, '&nbsp;').replace(/\t/g, '&nbsp;&nbsp;&nbsp;&nbsp;').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
    };
}
/// <reference path="../polyfill/string.ts" />
/// <reference path="../http/base.ts" />
/// <reference path="../lang.ts" />
var LP;
/// <reference path="../polyfill/string.ts" />
/// <reference path="../http/base.ts" />
/// <reference path="../lang.ts" />
(function (LP) {
    var tip;
    (function (tip) {
        tip.toast_interface = null;
        tip.alert_interface = null;
        tip.confirm_interface = null;
        tip.prompt_interface = null;
        function formatMessage(message) {
            return !(message instanceof Object) ? { content: message } : message;
        }
        tip.formatMessage = formatMessage;
        function toast(message, timeout) {
            if (timeout === void 0) { timeout = 1000; }
            var _message = formatMessage(message);
            return promiseWrap(function () {
                if (typeof tip.toast_interface == 'function')
                    return tip.toast_interface(_message, timeout);
                else
                    window.alert(_message.content.noHTML());
            });
        }
        tip.toast = toast;
        function alert(message, confirm_callback) {
            var _message = formatMessage(message);
            return promiseWrap(function () {
                if (typeof tip.alert_interface == 'function')
                    return tip.alert_interface(_message);
                else
                    window.alert(_message.content.noHTML());
            }).then(function () {
                if (confirm_callback && typeof confirm_callback == 'function')
                    return confirm_callback.call(void 0);
            });
        }
        tip.alert = alert;
        function confirm(message, confirm_callback, cancel_callback) {
            var _message = formatMessage(message);
            return promiseWrap(function () {
                if (typeof tip.confirm_interface == 'function')
                    return tip.confirm_interface(_message);
                else {
                    if (!window.confirm(_message.content.noHTML()))
                        return promiseReject();
                }
            }).then(function () {
                if (confirm_callback && typeof confirm_callback == 'function')
                    return confirm_callback.call(void 0);
            }, function (e) {
                return promiseReject.call(void 0, cancel_callback && typeof cancel_callback == 'function' ? cancel_callback.call(void 0) : e);
            });
        }
        tip.confirm = confirm;
        function prompt(message, confirm_callback, cancel_callback) {
            var _message = formatMessage(message);
            return promiseWrap(function () {
                if (typeof tip.prompt_interface == 'function')
                    return tip.prompt_interface(_message);
                else {
                    var v = window.prompt(_message.content.noHTML());
                    if (!v)
                        return promiseReject();
                    else
                        return v;
                }
            }).then(function (v) {
                if (confirm_callback && typeof confirm_callback == 'function')
                    return confirm_callback.call(void 0, v);
            }, function (e) {
                return promiseReject.call(void 0, cancel_callback && typeof cancel_callback == 'function' ? cancel_callback.call(void 0) : e);
            });
        }
        tip.prompt = prompt;
    })(tip = LP.tip || (LP.tip = {}));
})(LP || (LP = {}));
/// <reference path="../polyfill/string.ts" />
/// <reference path="../lang.ts" />
/// <reference path="./base.ts" />
var LP;
/// <reference path="../polyfill/string.ts" />
/// <reference path="../lang.ts" />
/// <reference path="./base.ts" />
(function (LP) {
    var tip;
    (function (tip) {
        tip.diy_interface = null;
        function diy(message, result, tipType) {
            var _message = tip.formatMessage(message);
            return promiseWrap(function () {
                if (typeof tip.diy_interface == 'function')
                    return tip.diy_interface(_message, result, tipType);
                else
                    return window.alert(_message.content.noHTML());
            });
        }
        function json(result, message, tipType) {
            if (typeof message == 'undefined' || typeof tipType != 'object')
                return;
            else if (typeof message == 'string')
                message = { content: message };
            diy(message, result, tipType);
            switch (tipType.type) {
                case 'redirect':
                    setTimeout(function () {
                        self.location.href = tipType.url;
                    }, tipType.timeout);
                    break;
                case 'refresh':
                    setTimeout(function () {
                        self.location.reload();
                        self.location.href = self.location.href;
                    }, tipType.timeout);
                    break;
                case 'back':
                case 'toast':
                    break;
            }
        }
        tip.json = json;
    })(tip = LP.tip || (LP.tip = {}));
})(LP || (LP = {}));
/// <reference path="../lang.ts" />
/// <reference path="../sec/encryptor.ts" />
/// <reference path="../tip/json.ts" />
var LP;
/// <reference path="../lang.ts" />
/// <reference path="../sec/encryptor.ts" />
/// <reference path="../tip/json.ts" />
(function (LP) {
    var http;
    (function (http) {
        var TIP_MASK;
        (function (TIP_MASK) {
            TIP_MASK[TIP_MASK["ALERT_CLIENT_ERROR"] = 1] = "ALERT_CLIENT_ERROR";
            TIP_MASK[TIP_MASK["ALERT_SERVER_ERROR"] = 2] = "ALERT_SERVER_ERROR";
            TIP_MASK[TIP_MASK["ALERT_SUCCESS"] = 4] = "ALERT_SUCCESS";
            TIP_MASK[TIP_MASK["ALERT_ERROR"] = 3] = "ALERT_ERROR";
            TIP_MASK[TIP_MASK["ALERT_ALL"] = 7] = "ALERT_ALL";
        })(TIP_MASK = http.TIP_MASK || (http.TIP_MASK = {}));
        function objectToForm(data) {
            var formData;
            if (data instanceof FormData)
                formData = data;
            else {
                formData = new FormData();
                for (var k in data)
                    formData.append(k, data[k]);
            }
            return formData;
        }
        http.objectToForm = objectToForm;
        var Base = /** @class */ (function () {
            function Base() {
                this.tipMask = 0;
                this.encryptor = new LP.sec.Encryptor();
                this.commonHeaders = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': Base.getCSRF(),
                    'X-RSA': encodeURIComponent(this.encryptor.getPublicKey()),
                };
                this.headers = {};
                this.tipMask = 0;
            }
            Base.getInstance = function () {
                return new this;
            };
            Base.prototype.alertMask = function (mask) {
                this.tipMask |= mask;
                return this;
            };
            Base.prototype.alertAll = function (tip) {
                if (tip === void 0) { tip = true; }
                this.tipMask = tip ? TIP_MASK.ALERT_ALL : 0;
                return this;
            };
            Base.prototype.alertClientError = function (tip) {
                if (tip === void 0) { tip = true; }
                if (tip)
                    this.tipMask |= TIP_MASK.ALERT_CLIENT_ERROR;
                else
                    this.tipMask ^= TIP_MASK.ALERT_CLIENT_ERROR;
                return this;
            };
            Base.prototype.alertServerError = function (tip) {
                if (tip === void 0) { tip = true; }
                if (tip)
                    this.tipMask |= TIP_MASK.ALERT_SERVER_ERROR;
                else
                    this.tipMask ^= TIP_MASK.ALERT_SERVER_ERROR;
                return this;
            };
            Base.prototype.alertError = function (tip) {
                if (tip === void 0) { tip = true; }
                if (tip)
                    this.tipMask |= TIP_MASK.ALERT_ERROR;
                else
                    this.tipMask ^= TIP_MASK.ALERT_ERROR;
                return this;
            };
            Base.prototype.alertSuccess = function (tip) {
                if (tip === void 0) { tip = true; }
                if (tip)
                    this.tipMask |= TIP_MASK.ALERT_SUCCESS;
                else
                    this.tipMask ^= TIP_MASK.ALERT_SUCCESS;
                return this;
            };
            Base.getCSRF = function () {
                var dom = document.querySelector('meta[name="csrf-token"]');
                return dom ? dom.getAttribute('content') : '';
            };
            Base.prototype.setHeader = function (key, value) {
                if (value == null)
                    this.headers = key;
                else
                    this.headers[key.toString()] = value;
                return this;
            };
            Base.prototype.setConfig = function (key, value) {
                var obj = this.config, keys = key.split('.');
                for (var i = 0; i < keys.length - 1; i++) {
                    var k = keys[i];
                    obj[k] = typeof obj[k] != 'undefined' ? obj[k] : {};
                    obj = obj[k];
                }
                obj[keys[keys.length - 1]] = value;
                return this;
            };
            Base.prototype.request = function (method, url, data) {
                var _this = this;
                if (this.encryptor.actived())
                    this.decryptHandler();
                return this.requestHandler({
                    method: method,
                    url: url,
                    data: data,
                    headers: this.headers
                }, this.config).then(function (json) {
                    if (json instanceof Object && typeof json.result != 'undefined') {
                        if (json.result == 'success' || json.result == 'api') {
                            if (_this.tipMask & TIP_MASK.ALERT_SUCCESS)
                                _this.errorHandler(json);
                            return json;
                        }
                        else {
                            return promiseReject(json);
                        }
                    }
                    return json;
                }).catch(function (e) {
                    if ((_this.tipMask & TIP_MASK.ALERT_SERVER_ERROR) && e instanceof Object && typeof e.result != 'undefined')
                        _this.errorHandler(e);
                    else if ((_this.tipMask & TIP_MASK.ALERT_CLIENT_ERROR) && typeof e['result'] == 'undefined')
                        _this.errorHandler(e);
                    return promiseReject(e);
                });
            };
            Base.prototype.get = function (url, data) {
                return this.request('get', url, data);
            };
            Base.prototype.post = function (url, data) {
                return this.request('post', url, data);
            };
            Base.prototype.head = function (url, data) {
                return this.request('head', url, data);
            };
            Base.prototype.options = function (url, data) {
                return this.request('options', url, data);
            };
            Base.prototype.patch = function (url, data) {
                return this.request('patch', url, data);
            };
            Base.prototype.put = function (url, data) {
                return this.request('put', url, data);
            };
            Base.prototype.delete = function (url, data) {
                return this.request('delete', url, data);
            };
            return Base;
        }());
        http.Base = Base;
    })(http = LP.http || (LP.http = {}));
})(LP || (LP = {}));
var LP;
(function (LP) {
    var http;
    (function (http) {
        var axiosAjax = /** @class */ (function (_super) {
            __extends(axiosAjax, _super);
            function axiosAjax(baseURL, timeout) {
                if (timeout === void 0) { timeout = 20000; }
                var _this = _super.call(this) || this;
                _this.instance = axios.create({
                    baseURL: baseURL == null ? '' : baseURL,
                    timeout: timeout,
                    headers: _this.commonHeaders,
                    responseType: 'json',
                    xsrfHeaderName: 'X-CSRF-TOKEN',
                    xsrfCookieName: 'XSRF-TOKEN',
                    // `transformRequest` allows changes to the request data before it is sent to the server
                    // This is only applicable for request methods 'PUT', 'POST', and 'PATCH'
                    // The last function in the array must return a string or an instance of Buffer, ArrayBuffer,
                    // FormData or Stream
                    // You may modify the headers object.
                    transformRequest: [function (data, headers) {
                            // Do whatever you want to transform the data
                            return data;
                        }],
                    // `transformResponse` allows changes to the response data to be made before
                    // it is passed to then/catch
                    transformResponse: [function (data) {
                            // Do whatever you want to transform the data
                            return data;
                        }],
                });
                return _this;
            }
            axiosAjax.prototype.requestHandler = function (config, extra) {
                var params = config.method.toLowerCase() == 'get' ? config.data : {};
                if (config.method.toLowerCase() == 'get')
                    config.data = null;
                var c = {
                    method: config.method.toLowerCase(),
                    url: config.url,
                    params: params,
                    data: config.data,
                    headers: config.headers
                };
                var _c = extend(true, {}, c, extra);
                return this.instance.request(_c);
            };
            axiosAjax.prototype.decryptHandler = function () {
                var t = this;
                this.instance.defaults.transformResponse = [
                    function (data) {
                        var json = data;
                        try {
                            json = t.encryptor.decrypt(json);
                            data = json;
                        }
                        catch (e) {
                            console.log(e);
                        }
                        return data;
                    }
                ];
            };
            axiosAjax.prototype.errorHandler = function (e) {
                if (e instanceof Error) {
                }
                else if (e instanceof Object && typeof e.result != 'undefined') {
                    LP.tip.json(e.result, e.message, e.tipType);
                }
            };
            axiosAjax.get = function (url, data) {
                var q = new axiosAjax();
                return q.request('get', url, data);
            };
            axiosAjax.post = function (url, data) {
                var q = new axiosAjax();
                return q.request('post', url, data);
            };
            axiosAjax.form = function (url, data) {
                var q = new axiosAjax();
                return q.request('post', url, http.objectToForm(data));
            };
            axiosAjax.head = function (url, data) {
                var q = new axiosAjax();
                return q.request('head', url, data);
            };
            axiosAjax.options = function (url, data) {
                var q = new axiosAjax();
                return q.request('options', url, data);
            };
            axiosAjax.patch = function (url, data) {
                var q = new axiosAjax();
                return q.request('patch', url, data);
            };
            axiosAjax.put = function (url, data) {
                var q = new axiosAjax();
                return q.request('put', url, data);
            };
            axiosAjax.delete = function (url, data) {
                var q = new axiosAjax();
                return q.request('delete', url, data);
            };
            return axiosAjax;
        }(http.Base));
        http.axiosAjax = axiosAjax;
    })(http = LP.http || (LP.http = {}));
})(LP || (LP = {}));
if (jQuery) {
    jQuery.fn.extend({
        query: function (callback, tipMask) {
            if (tipMask === void 0) { tipMask = LP.http.TIP_MASK.ALERT_ALL; }
            return this.each(function () {
                var $this = jQuery(this);
                var is_form = $this.is('form');
                var on = $this.data('lp-query');
                if (on)
                    $this.off(is_form ? 'submit' : 'click', on);
                if (callback == 'destroy')
                    return;
                //bind
                var validator = is_form ? $this.data('validator') : null;
                if (validator)
                    validator.settings.submitHandler = function (f, e) { };
                on = function (e) {
                    if ($this.is('.disabled,[disabled]'))
                        return false;
                    var selector = $this.attr('selector');
                    var $selector = is_form ? $this.add(selector) : jQuery(selector);
                    if (validator && !jQuery.isEmptyObject(validator.invalid))
                        return false;
                    if ((selector || is_form) && $selector.serializeArray().length <= 0) {
                        LP.tip.toast(LP.QUERY_LANGUAGE.unselected);
                        return false;
                    }
                    var url = $this.attr(is_form ? 'action' : 'href'), method = $this.attr('method'), msg = $this.attr('confirm');
                    var query = function () {
                        var $doms = is_form ? jQuery(':submit,:image', $this) /*.add($this)*/ : $this;
                        $doms = $doms.filter(':not(.disabled,[disabled])');
                        $doms.prop('disabled', true).attr('disabled', 'disabled').each(function () {
                            var $t = jQuery(this), o = $t.offset();
                            jQuery('<div style="position:absolute;left:' + (o.left + $t.width()) + 'px;top:' + (o.top - 16) + 'px;height:32px;width:32px;display:block;z-index:99999" class="query-loading"><img style="width:32px;height:32px;" src="data:image/svg+xml;utf8,%3Csvg width=\'57\' height=\'57\' viewBox=\'0 0 57 57\' xmlns=\'http://www.w3.org/2000/svg\' stroke=\'#fff\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg transform=\'translate(1 1)\' stroke-width=\'2\'%3E%3Ccircle cx=\'5\' cy=\'50\' r=\'5\' stroke=\'#4285F4\'%3E%3Canimate attributeName=\'cy\' begin=\'0s\' dur=\'2.2s\' values=\'50;5;50;50\' calcMode=\'linear\' repeatCount=\'indefinite\' /%3E%3Canimate attributeName=\'cx\' begin=\'0s\' dur=\'2.2s\' values=\'5;27;49;5\' calcMode=\'linear\' repeatCount=\'indefinite\' /%3E%3C/circle%3E%3Ccircle cx=\'27\' cy=\'5\' r=\'5\' stroke=\'#DE3E35\'%3E%3Canimate attributeName=\'cy\' begin=\'0s\' dur=\'2.2s\' from=\'5\' to=\'5\' values=\'5;50;50;5\' calcMode=\'linear\' repeatCount=\'indefinite\' /%3E%3Canimate attributeName=\'cx\' begin=\'0s\' dur=\'2.2s\' from=\'27\' to=\'27\' values=\'27;49;5;27\' calcMode=\'linear\' repeatCount=\'indefinite\' /%3E%3C/circle%3E%3Ccircle cx=\'49\' cy=\'50\' r=\'5\' stroke=\'#F7C223\'%3E%3Canimate attributeName=\'cy\' begin=\'0s\' dur=\'2.2s\' values=\'50;50;5;50\' calcMode=\'linear\' repeatCount=\'indefinite\' /%3E%3Canimate attributeName=\'cx\' from=\'49\' to=\'49\' begin=\'0s\' dur=\'2.2s\' values=\'49;5;27;49\' calcMode=\'linear\' repeatCount=\'indefinite\' /%3E%3C/circle%3E%3C/g%3E%3C/g%3E%3C/svg%3E"></div>').appendTo('body');
                        }); //disabled the submit button
                        var data = $selector.serializeArray();
                        var $files = jQuery('input[type="file"]:not([name=""])', $selector); // all files
                        if ($selector.is('[enctype="multipart/form-data"]') || $files.length > 0) {
                            var _formData_1 = new FormData();
                            data.forEach(function (v) { return _formData_1.append(v.name, v.value); });
                            $files.each(function () {
                                var _this = this;
                                jQuery.each(this.files, function (i, file) { return _formData_1.append(jQuery(_this).attr('name'), file); });
                            });
                            data = _formData_1;
                        }
                        return LP.http.jQueryAjax.getInstance().alertMask(tipMask).request(method, url, data).then(function (json) {
                            if (typeof callback != 'undefined' && jQuery.isFunction(callback))
                                callback.call($this, json);
                        }).finally(function () {
                            jQuery('.query-loading').remove();
                            $doms.prop('disabled', false).removeAttr('disabled');
                        });
                    };
                    if (msg) {
                        msg = msg.replace('%L', $selector.serializeArray().length.toString());
                        LP.tip.confirm(msg, query);
                    }
                    else {
                        query();
                    }
                    e.stopImmediatePropagation();
                    return false;
                };
                $this.on(is_form ? 'submit' : 'click', on).data({ 'lp-query': on });
            });
        }
    });
}
var LP;
(function (LP) {
    var http;
    (function (http) {
        var jQueryAjax = /** @class */ (function (_super) {
            __extends(jQueryAjax, _super);
            function jQueryAjax(timeout) {
                if (timeout === void 0) { timeout = 20000; }
                var _this = _super.call(this) || this;
                jQuery.ajaxSetup({
                    headers: _this.commonHeaders,
                    timeout: timeout
                });
                return _this;
            }
            jQueryAjax.prototype.requestHandler = function (config, extra) {
                var _headers = config.headers, _data = config.data;
                if (typeof jQuery['deparam'] != 'undefined' && config.data && config.data instanceof String)
                    _data = jQuery['deparam'](config.data);
                //使用POST模拟的PUT或者DELETE等
                if (_data && _data._method) {
                    config.method = _data._method;
                    _headers['X-HTTP-Method-Override'] = config.method;
                }
                if (_data && _data._token)
                    _headers['X-CSRF-TOKEN'] = _data._token;
                return new Promise(function (resolve, reject) {
                    var c = {
                        url: config.url,
                        data: _data ? _data : null,
                        async: true,
                        cache: false,
                        type: config.method.toUpperCase(),
                        method: config.method.toUpperCase(),
                        headers: _headers,
                        processData: _data instanceof FormData ? false : true,
                        dataType: /[\?&](jsonp|callback)=\?/i.test(config.url) ? 'jsonp' : 'json',
                        success: function (json, textStatus, jqXHR) {
                            resolve(json);
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            reject([].slice.call(arguments));
                        }
                    };
                    var _c = extend(true, {}, c, extra);
                    _c['beforeSend'] = function (xhr) {
                        if (typeof _headers['Authorization'] != 'undefined')
                            xhr.setRequestHeader('Authorization', _headers['Authorization']);
                        if (_c.processData === false && xhr.overrideMimeType)
                            xhr.overrideMimeType("multipart/form-data");
                    };
                    if (_c.processData === false) {
                        _c['enctype'] = 'multipart/form-data';
                        _c['contentType'] = false;
                        _c['mimeType'] = 'multipart/form-data';
                    }
                    jQuery.ajax(_c);
                });
            };
            jQueryAjax.prototype.decryptHandler = function () {
                var t = this;
                jQuery.ajaxSetup({
                    dataFilter: function (data, type) {
                        if (type.toLowerCase() == 'json') {
                            var json = void 0;
                            try {
                                json = jQuery.parseJSON(data);
                                json = t.encryptor.decrypt(json);
                                data = JSON.stringify(json);
                                if (typeof json.debug != 'undefined' && !!json.debug)
                                    console.log(json);
                            }
                            catch (e) {
                                console.log(e);
                            }
                        }
                        return data;
                    }
                });
            };
            jQueryAjax.prototype.errorHandler = function (e) {
                if (e instanceof Array) {
                    var xhr = e[0];
                    var textStatus = e[1];
                    switch (textStatus) {
                        case 'timeout':
                            LP.tip.toast(LP.QUERY_LANGUAGE.network_timeout);
                            break;
                        case 'error':
                            if (xhr instanceof Object && typeof xhr.responseJSON != 'undefined') {
                                var json = xhr.responseJSON;
                                if (json instanceof Object && typeof json.result != 'undefined') {
                                    LP.tip.json(json.result, json.message, json.tipType);
                                }
                            }
                            break;
                        case 'parsererror':
                            LP.tip.toast(LP.QUERY_LANGUAGE.parser_error);
                            break;
                        //case 'notmodified':
                        case 'abort':
                        default:
                            LP.tip.toast(LP.QUERY_LANGUAGE.server_error);
                            break;
                    }
                }
                else if (e instanceof Object && typeof e.result != 'undefined') {
                    LP.tip.json(e.result, e.message, e.tipType);
                }
            };
            jQueryAjax.get = function (url, data) {
                var q = new jQueryAjax();
                return q.request('get', url, data);
            };
            jQueryAjax.post = function (url, data) {
                var q = new jQueryAjax();
                return q.request('post', url, data);
            };
            jQueryAjax.form = function (url, $form) {
                var q = new jQueryAjax();
                var data = $form.serializeArray();
                var $files = jQuery('input[type="file"]:not([name=""])', $form); // all files
                if ($form.is('[enctype="multipart/form-data"]') || $files.length > 0) {
                    var _formData_2 = new FormData();
                    data.forEach(function (v) { return _formData_2.append(v.name, v.value); });
                    $files.each(function () {
                        var _this = this;
                        jQuery.each(this.files, function (i, file) { return _formData_2.append(jQuery(_this).attr('name'), file); });
                    });
                    data = _formData_2;
                }
                return q.request($form.attr('method'), $form.attr('action'), data);
            };
            jQueryAjax.head = function (url, data) {
                var q = new jQueryAjax();
                return q.request('head', url, data);
            };
            jQueryAjax.options = function (url, data) {
                var q = new jQueryAjax();
                return q.request('options', url, data);
            };
            jQueryAjax.patch = function (url, data) {
                var q = new jQueryAjax();
                return q.request('patch', url, data);
            };
            jQueryAjax.put = function (url, data) {
                var q = new jQueryAjax();
                return q.request('put', url, data);
            };
            jQueryAjax.delete = function (url, data) {
                var q = new jQueryAjax();
                return q.request('delete', url, data);
            };
            return jQueryAjax;
        }(http.Base));
        http.jQueryAjax = jQueryAjax;
    })(http = LP.http || (LP.http = {}));
})(LP || (LP = {}));
/**
 * 将一个Array或者Object按树形结构alert出来、或返回
 *
 * @param  {Objbect/Array} array      传入的数组或者对象
 * @param  {Boolean} return_val 是否返回，默认是alert出
 * @return {String}             树形结构
 */
function print_r(array, return_val) {
    if (return_val === void 0) { return_val = true; }
    var output = '', pad_char = ' ', pad_val = 4, getFuncName = function (fn) {
        var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
        if (!name)
            return '(Anonymous)';
        return name[1];
    };
    var repeat_char = function (len, pad_char) {
        var str = '';
        for (var i = 0; i < len; i++)
            str += pad_char;
        return str;
    };
    var formatArray = function (obj, cur_depth, pad_val, pad_char) {
        if (cur_depth > 0)
            cur_depth++;
        var base_pad = repeat_char(pad_val * cur_depth, pad_char);
        var thick_pad = repeat_char(pad_val * (cur_depth + 1), pad_char);
        var str = '';
        if (typeof obj === 'object' && obj !== null && obj.constructor && getFuncName(obj.constructor) !== 'PHPJS_Resource') {
            str += 'Array\n' + base_pad + '(\n';
            for (var key in obj) {
                if (Object.prototype.toString.call(obj[key]) === '[object Array]')
                    str += thick_pad + '[' + key + '] => ' + formatArray(obj[key], cur_depth + 1, pad_val, pad_char);
                else
                    str += thick_pad + '[' + key + '] => ' + obj[key] + '\n';
            }
            str += base_pad + ')\n';
        }
        else if (obj === null || obj === undefined)
            str = '';
        else
            str = obj.toString();
        return str;
    };
    output = formatArray(array, 0, pad_val, pad_char);
    if (return_val !== true) {
        alert(output);
        return output;
    }
    return output;
}
/**
 * Extends the object in the first argument using the object in the second argument.
 * @method extend
 * @param {boolean} deep
 * @param {} obj
 * @return {} obj extended
 */
function extend(deep, obj) {
    var args = [];
    for (var _i = 2; _i < arguments.length; _i++) {
        args[_i - 2] = arguments[_i];
    }
    var argsStart, deepClone, firstArg;
    if (typeof deep === 'boolean') {
        argsStart = 2;
        deepClone = deep;
        firstArg = obj;
    }
    else {
        argsStart = 1;
        firstArg = deep;
        deepClone = true;
    }
    for (var i = argsStart; i < arguments.length; i++) {
        var source = arguments[i];
        if (source) {
            for (var prop in source) {
                if (deepClone && source[prop] && source[prop].constructor === Object) {
                    if (!firstArg[prop] || firstArg[prop].constructor === Object) {
                        firstArg[prop] = firstArg[prop] || {};
                        extend(firstArg[prop], deepClone, source[prop]);
                    }
                    else {
                        firstArg[prop] = source[prop];
                    }
                }
                else {
                    firstArg[prop] = source[prop];
                }
            }
        }
    }
    return firstArg;
}
//# sourceMappingURL=lp.js.map
