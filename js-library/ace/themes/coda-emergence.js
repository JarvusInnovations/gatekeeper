define("ace/theme/coda-emergence",["require","exports","module"],function(a,b,c){b.isDark=!0,
b.cssClass="ace-coda-emergence",
b.cssText=".ace-coda.emergence {\
    font-family: panic;\
}\
.ace-line {\
    font-family: panic;\
}\
.ace-coda-emergence .ace_editor {\
  border: 2px solid rgb(159, 159, 159);\
}\
\
.ace-coda-emergence .ace_editor.ace_focus {\
  border: 2px solid #327fbd;\
}\
\
.ace-coda-emergence .ace_gutter {\
  width: 50px;\
  background: #e8e8e8;\
  color: #333;\
  overflow : hidden;\
  font-family: panic;\
}\
\
.ace-coda-emergence .ace_gutter-layer {\
  width: 100%;\
  text-align: right;\
}\
\
.ace-coda-emergence .ace_gutter-layer .ace_gutter-cell {\
  padding-right: 6px;\
}\
\
.ace-coda-emergence .ace_print_margin {\
  width: 1px;\
  background: #e8e8e8;\
}\
\
.ace-coda-emergence .ace_scroller {\
  background-color: #000000;\
}\
\
.ace-coda-emergence .ace_text-layer {\
  cursor: text;\
  color: #E6E1DC;\
}\
\
.ace-coda-emergence .ace_cursor {\
  border-left: 2px solid #FFFFFF;\
}\
\
.ace-coda-emergence .ace_cursor.ace_overwrite {\
  border-left: 0px;\
  border-bottom: 1px solid #FFFFFF;\
}\
 \
.ace-coda-emergence .ace_marker-layer .ace_selection {\
  background: #494949;\
}\
\
.ace-coda-emergence .ace_marker-layer .ace_step {\
  background: rgb(198, 219, 174);\
}\
\
.ace-coda-emergence .ace_marker-layer .ace_bracket {\
  margin: -1px 0 0 -1px;\
  border: 1px solid #FCE94F;\
}\
\
.ace-coda-emergence .ace_marker-layer .ace_active_line {\
  background: #222;\
}\
\
       \
.ace-coda-emergence .ace_invisible {\
  color: #404040;\
}\
\
.ace-coda-emergence .ace_keyword {\
  color: #ec77b4;\
}\
\
.ace-coda-emergence .ace_keyword.ace_operator {\
  color: #9e5e77;\
}\
\
.ace-coda-emergence .ace_constant {\
  color:#68C1D8;\
}\
\
.ace-coda-emergence .ace_constant.ace_language {\
  color:#E1C582;\
}\
\
.ace-coda-emergence .ace_constant.ace_library {\
  color:#8EC65F;\
}\
\
.ace-coda-emergence .ace_constant.ace_numeric {\
  color:#7FC578;\
}\
\
.ace-coda-emergence .ace_invalid {\
  color:#FFFFFF;\
  background-color:#FE3838;\
}\
\
.ace-coda-emergence .ace_invalid.ace_illegal {\
  \
}\
\
.ace-coda-emergence .ace_invalid.ace_deprecated {\
  color:#FFFFFF;\
  background-color:#FE3838;\
}\
\
.ace-coda-emergence .ace_support {\
  \
}\
\
.ace-coda-emergence .ace_support.ace_function {\
  color:#FC803A;\
}\
\
.ace-coda-emergence .ace_function.ace_buildin {\
  \
}\
\
.ace-coda-emergence .ace_string {\
  color:#ff8714;\
}\
\
.ace-coda-emergence .ace_string.ace_regexp {\
  \
}\
\
.ace-coda-emergence .ace_comment {\
  color:#91dc93;\
}\
\
.ace-coda-emergence .ace_comment.ace_doc {\
  \
}\
\
.ace-coda-emergence .ace_comment.ace_doc.ace_tag {\
  \
}\
\
.ace-coda-emergence .ace_variable {\
  \
}\
\
.ace-coda-emergence .ace_variable.ace_language {\
  \
}\
\
.ace-coda-emergence .ace_xml_pe {\
  \
}";var d=a("../lib/dom");d.importCssString(b.cssText)})