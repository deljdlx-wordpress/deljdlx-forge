define("ace/mode/taverne_highlight_rules", ["require", "exports", "module", "ace/lib/oop", "ace/mode/text_highlight_rules"], function (require, exports, module) {
    "use strict";
    var oop = require("../lib/oop");
    var TextHighlightRules = require("./text_highlight_rules").TextHighlightRules;
    var TaverneHighlightRules = function () {
        this.$rules = {
            "start": [
                {
                    token: "comment",
                    regex: /^\s*#.*$/
                },
                // {
                //     token: "keyword", // negated patterns
                //     // regex: /^\s*!.*$/
                //     regex: /\$\{.*?\}/
                // },
                {
                    token: "constant.numeric",
                    regex: /\b\d+\b/
                },
                {
                    // detect constants
                    token: "constant.language",
                    regex: /\$\{__.*?\}/
                },
                {
                    // detect variables
                    token: "variable.language",
                    regex: /\$\{.*?\}/
                }
            ]
        };
        this.normalizeRules();
    };
    TaverneHighlightRules.metaData = {
        fileTypes: ['taverne'],
        name: 'Taverne'
    };
    oop.inherits(TaverneHighlightRules, TextHighlightRules);
    exports.TaverneHighlightRules = TaverneHighlightRules;

});








define("ace/mode/taverne", ["require", "exports", "module", "ace/lib/oop", "ace/mode/text", "ace/mode/taverne_highlight_rules"], function (require, exports, module) {
    "use strict";
    var oop = require("../lib/oop");
    var TextMode = require("./text").Mode;
    var TaverneHighlightRules = require("./taverne_highlight_rules").TaverneHighlightRules;
    var Mode = function () {
        this.HighlightRules = TaverneHighlightRules;
        this.$behaviour = this.$defaultBehaviour;
    };
    oop.inherits(Mode, TextMode);
    (function () {
        this.lineCommentStart = "#";
        this.$id = "ace/mode/taverne";


        // it works, but we need live autocompletion
        // this.getCompletions = function (state, session, pos, prefix) {
        //     return [
        //         {
        //             caption: 'test',
        //             value: 'test',
        //             meta: 'test'
        //         }
        //     ]
        // };


    }).call(Mode.prototype);
    exports.Mode = Mode;

}); (function () {
    window.require(["ace/mode/taverne"], function (m) {
        if (typeof module == "object" && typeof exports == "object" && module) {
            module.exports = m;
        }
    });
})();
