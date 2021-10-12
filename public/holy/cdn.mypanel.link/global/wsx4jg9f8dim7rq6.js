var custom = new function() {
var i = this;
i.request = null, i.confirm = function(e, t, a, n) {
t = (0, templates["modal/confirm"])($.extend({}, !0, {
confirm_button: "OK",
cancel_button: "Cancel",
width: "600px"
}, n, {
title: e,
confirm_message: t
}));
return $(window.document.body).append(t), $("#confirmModal").modal({}), $("#confirmModal").on("hidden.bs.modal", function(e) {
$("#confirmModal").remove();
}), $("#confirm_yes").on("click", function(e) {
return $("#confirm_yes").unbind("click"), $("#confirmModal").modal("hide"), a.call();
});
}, i.alert = function(e, t, a) {
t = (0, templates["modal/alert"])($.extend({}, !0, {
width: "600px"
}, a, {
title: e,
alert_message: t
}));
$(window.document.body).append(t), $("#alertModal").modal({}), $("#alertModal").on("hidden.bs.modal", function(e) {
$("#alertModal").remove();
});
}, i.ajax = function(e) {
var t = $.extend({}, !0, e);
"object" == typeof e && (e.beforeSend = function() {
"function" == typeof t.beforeSend && t.beforeSend();
}, e.success = function(e) {
i.request = null, e.redirect && 0 < e.redirect.length ? window.location.replace(e.redirect) : "function" == typeof t.success && t.success(e);
}, null != i.request && i.request.abort(), i.request = $.ajax(e));
}, i.notify = function(e) {
var t, a;
if ($("body").addClass("bottom-right"), "object" != typeof e) return !1;
for (t in e) void 0 !== (a = $.extend({}, !0, {
type: "success",
delay: 8e3,
text: ""
}, e[t])).text && null != a.text && $.notify({
message: a.text.toString()
}, {
type: a.type,
placement: {
from: "bottom",
align: "right"
},
z_index: 2e3,
delay: a.delay,
animate: {
enter: "animated fadeInDown",
exit: "animated fadeOutUp"
}
});
}, i.sendBtn = function(t, a) {
var n, e;
"object" != typeof a && (a = {}), t.hasClass("active") || (void 0 === (n = $.extend({}, !0, a)).spinnerLocation && (n.spinnerLocation = t), 
n.spinnerLocation.addClass("has-spinner"), n.url = t.attr("href") || t.data("url"), 
void 0 !== n.type && n.type.toUpperCase() === "POST".toUpperCase() && "undefined" != typeof yii && ((e = {})[yii.getCsrfParam()] = yii.getCsrfToken(), 
n.data = $.extend({}, n.data, e)), $(".spinner", n.spinnerLocation).remove(), n.spinnerLocation.prepend('<span class="spinner"><i class="fa fa-spinner fa-spin"></i></span>'), 
n.beforeSend = function() {
t.addClass("active");
}, n.success = function(e) {
t.removeClass("active"), $(".spinner", n.spinnerLocation).remove(), "success" === e.status ? "function" == typeof a.callback && a.callback(e) : "error" === e.status && ("function" == typeof a.errorCallback ? a.errorCallback(e) : i.notify({
0: {
type: "danger",
text: e.message
}
}));
}, i.ajax(n));
}, i.sendFrom = function(t, a, n) {
var e, o;
"object" != typeof n && (n = {}), t.hasClass("active") || (t.addClass("has-spinner"), 
e = $.extend({}, !0, n), o = $(".error-summary", a), e.url = a.attr("action"), e.type = "POST", 
$(".spinner", t).remove(), t.prepend('<span class="spinner"><i class="fa fa-spinner fa-spin"></i></span>'), 
e.beforeSend = function() {
t.addClass("active"), custom.showModalLoader(!0), o.length && (o.addClass("hidden"), 
o.html(""));
}, e.success = function(e) {
t.removeClass("active"), custom.showModalLoader(!1), $(".spinner", t).remove(), 
"success" == e.status ? "function" == typeof n.callback && n.callback(e) : "error" == e.status && (e.message && (o.length ? (o.html(e.message), 
o.removeClass("hidden")) : i.notify({
0: {
type: "danger",
text: e.message
}
})), e.errors && $.each(e.errors, function(e, t) {
a.yiiActiveForm("updateAttribute", e, t);
}), "function" == typeof n.errorCallback && n.errorCallback(e));
}, i.ajax(e));
}, i.generatePassword = function(e) {
void 0 === e && (e = 8);
for (var t = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789", a = "", n = t.length, o = 0; o < e; ++o) a += t.charAt(Math.floor(Math.random() * n));
return a;
}, i.freezeForm = function(e) {
var t, a, n = {};
return e.find("input, select, textarea").each(function() {
this.name && (t = this.name, "checkbox" === (a = $(this)).attr("type") || "radio" === a.attr("type") ? n[t] = a.prop("checked") : n[t] = a.val());
}), JSON.stringify(n);
}, i.restoreForm = function(e, t) {
var a, n, o = JSON.parse(e);
t.find("input, select, textarea").each(function() {
this.name && (a = this.name, "hidden" !== (n = $(this)).attr("type") && ("checkbox" === n.attr("type") || "radio" === n.attr("type") ? n.prop("checked", o[a]) : n.val(o[a])));
});
}, i.showModalLoader = function(e) {
$(".modal-loader").toggleClass("hidden", !e);
}, i.buildFields = function(a, e) {
var n = this, o = "";
return $.each(e, function(e, t) {
o += n.buildField(a + "_" + e, t);
}), o;
}, i.buildField = function(e, t) {
var a = $("<label/>", {
class: "control-label",
for: e
}).text(t.label), t = "textarea" === t.type ? $("<textarea/>", {
rows: 7
}).text(t.value) : $("<input/>", {
type: "text",
value: t.value
});
return t.attr({
id: e,
class: "form-control",
readonly: !0
}), $("<div/>", {
class: "form-group"
}).append(a).append(t).wrap("<div/>").parent().html();
}, i.isInt = function(e, t) {
return void 0 !== t && "keyup" === t.type && "-" === t.key && "-" === e || !isNaN(e) && (0 | (e = parseFloat(e))) === e;
};
}(), customModule = {};

window.modules = {}, $(function() {
"object" == typeof window.modules && $.each(window.modules, function(e, t) {
void 0 !== customModule[e] && customModule[e].run(t);
});
});

var templates = {};

templates["addfunds/alert"] = _.template('<div class="alert alert-dismissible alert-danger ">\n    <button type="button" class="close" data-dismiss="alert">×</button>\n    <%= text %>\n</div>'), 
templates["addfunds/checkbox"] = _.template('<div class="form-group fields" id="order_<%= name %>">\n    <div class="form-group__checkbox">\n        <label class="form-group__checkbox-label">\n            <input name="AddFoundsForm[fields][<%= name %>]" value="0" type="hidden"/>\n            <input name="AddFoundsForm[fields][<%= name %>]" value="1" type="checkbox" id="field-<%= name %>"/>\n            <span class="checkmark"></span>\n        </label>\n        <label for="field-<%= name %>" class="form-group__label-title">\n            <%= label %>\n        </label>\n    </div>\n</div>'), 
templates["addfunds/cryptochill_payment_request_btn"] = _.template('<button type=\'button\' id="<%= id %>" data-amount="<%= amount %>" data-product="<%= product %>" data-passthrough="<%= passthrough %>" data-currency="<%= currency %>" class="hidden btn cryptochill-button"><%= product %></button>'), 
templates["addfunds/custom/credit_card"] = _.template('<div class="form-group fields">\n    <label class="control-label"><%= card_number.label %></label>\n    <input class="form-control" id="field-visible-<%= card_number.name %>" name="AddFoundsForm[fields][<%= card_number.name %>]" value="<%= card_number.value %>" type="text" autocomplete="off" placeholder="XXXX-XXXX-XXXX-XXXX" size="19" >\n</div>\n<div class="row">\n    <div class="col-md-4 form-group fields">\n        <label class="control-label"><%= expiry_month.label %></label>\n        <input class="form-control" id="field-visible-<%= expiry_month.name %>" name="AddFoundsForm[fields][<%= expiry_month.name %>]" value="<%= expiry_month.value %>" placeholder="MM" minlength="2" maxlength="2" type="number">\n    </div>\n    <div class="col-md-4 form-group fields">\n        <label class="control-label"><%= expiry_year.label %></label>\n        <input class="form-control" id="field-visible-<%= expiry_year.name %>" name="AddFoundsForm[fields][<%= expiry_year.name %>]" value="<%= expiry_year.value %>" placeholder="YY" minlength="2" maxlength="2" type="number">\n    </div>\n    <div class="col-md-4 form-group fields">\n        <label class="control-label"><%= cvv.label %></label>\n        <input autocomplete="on" class="form-control" id="field-visible-<%= cvv.name %>" name="AddFoundsForm[fields][<%= cvv.name %>]" value="<%= cvv.value %>" maxlength="4" type="password">\n    </div>\n</div>'), 
templates["addfunds/description"] = _.template('<div class="form-group fields" id="order_<%= name %>">\n    <label class="control-label" for="field-<%= name %>"><%= label %></label>\n    <div class="panel-body border-solid border-rounded text-center"><%= value %></div>\n</div>'), 
templates["addfunds/hidden"] = _.template('<input class="fields" name="AddFoundsForm[fields][<%= name %>]" value="<%= value %>" type="hidden" id="field-<%= name %>"/>'), 
templates["addfunds/input"] = _.template('<div class="form-group fields" id="order_<%= name %>">\n    <label class="control-label" for="field-<%= name %>"><%= label %></label>\n    <input class="form-control" name="AddFoundsForm[fields][<%= name %>]" value="<%= value %>" type="text" id="field-<%= name %>"/>\n</div>'), 
templates["addfunds/modal/adyen_modal"] = _.template('<div class="modal fade" id="adyenModal" data-backdrop="static" tabindex="-1" role="dialog">\n    <div class="modal-dialog" role="document">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-label="<%= modal_title %>">\n                    <span aria-hidden="true">&times;</span>\n                </button>\n                <h4 class="modal-title"><%= modal_title %></h4>\n            </div>\n            <div class="modal-body">\n                <div id="dropin-container"></div>\n            </div>\n        </div>\n    </div>\n</div>'), 
templates["addfunds/modal/checkout_com_card"] = _.template('<div class="modal fade" id="checkoutcomCardModal" data-backdrop="static" tabindex="-1" role="dialog">\n    <div class="modal-dialog" role="document">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-label="<%= modal_title %>">\n                    <span aria-hidden="true">&times;</span>\n                </button>\n                <h4 class="modal-title"><%= modal_title %></h4>\n            </div>\n            <form method="POST">\n                <div class="modal-body">\n                    <div class="frames-container">\n                        \x3c!-- form will be added here --\x3e\n                    </div>\n                    \x3c!-- add submit button --\x3e\n                </div>\n                <div class="modal-footer">\n                    <button type="submit" class="button-credit-card btn btn-primary">\n                        <%= submit_title %>\n                    </button>\n                    <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal">\n                        <%= cancel_title %>\n                    </button>\n                </div>\n            </form>\n        </div>\n    </div>\n</div>'), 
templates["addfunds/modal/gb_prime_pay_3ds"] = _.template('<style>\n    @keyframes spinner{\n        to{transform:rotate(360deg)}\n    }\n    .spinner-block__inline{display:inline-block}\n    .spinner-block__container{display:block;width:100%;height:558px}\n    .spinner-block__wrapper{position:relative;display:flex;align-items:center;justify-content:center}\n    .spinner-block__small{width:16px;height:16px}\n    .spinner-block__small span{font-size:14px}\n    .spinner-block__medium{width:24px;height:24px}\n    .spinner-block__medium span{font-size:24px}\n    .spinner-block__high{width:42px;height:42px}\n    .spinner-block__high span{font-size:42px}\n    .spinner-block__wrapper span{animation:spinner .6s linear infinite}\n</style>\n<div class="modal fade" id="gbPrimePay3dsCardModal" data-backdrop="static" tabindex="-1" role="dialog">\n    <div class="modal-dialog" role="document">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-label="<%= modal_title %>">\n                    <span aria-hidden="true">&times;</span>\n                </button>\n                <h4 class="modal-title"><%= modal_title %></h4>\n            </div>\n            <div class="modal-body">\n                    <div id="gb-form" style="height: 558px;">\n                        <div class="spinner-block__wrapper spinner-block__container" id="gb-modal-spinner">\n                            <div class="spinner-block__wrapper spinner-block__high">\n                                <span class="fal fa-spinner-third"></span>\n                            </div>\n                        </div>\n                    </div>\n            </div>\n            <div class="modal-footer">\n                <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal"><%= close_title %></button>\n            </div>\n        </div>\n    </div>\n</div>'), 
templates["addfunds/modal/qr_code_modal"] = _.template('<style>\n    @keyframes spinner{\n        to{transform:rotate(360deg)}\n    }\n    .spinner-block__inline{display:inline-block}\n    .spinner-block__container{display:block;width:100%;height:558px}\n    .spinner-block__wrapper{position:relative;display:flex;align-items:center;justify-content:center}\n    .spinner-block__small{width:16px;height:16px}\n    .spinner-block__small span{font-size:14px}\n    .spinner-block__medium{width:24px;height:24px}\n    .spinner-block__medium span{font-size:24px}\n    .spinner-block__high{width:42px;height:42px}\n    .spinner-block__high span{font-size:42px}\n    .spinner-block__wrapper span{animation:spinner .6s linear infinite}\n</style>\n<div class="modal fade" tabindex="-1" role="dialog" id="qr-modal" data-backdrop="static">\n    <div class="modal-dialog" role="document">\n        <form class="modal-content">\n            <div class="modal-body">\n                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n                <div class="text-center">\n                    <div class="spinner-block__wrapper spinner-block__container" id="qr-modal-spinner">\n                        <div class="spinner-block__wrapper spinner-block__high">\n                            <span class="fal fa-spinner-third"></span>\n                        </div>\n                    </div>\n                    <img alt="" class="img-responsive center-block" id="qr-code-image">\n                </div>\n            </div>\n            <div class="modal-footer">\n                <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal"><%= close_title %></button>\n            </div>\n        </form>\n    </div>\n</div>'), 
templates["addfunds/modal/qr_code_modal_canvas"] = _.template('<style>\n    @keyframes spinner{\n        to{transform:rotate(360deg)}\n    }\n    .spinner-block__inline{display:inline-block}\n    .spinner-block__container{display:block;width:100%;height:558px}\n    .spinner-block__wrapper{position:relative;display:flex;align-items:center;justify-content:center}\n    .spinner-block__small{width:16px;height:16px}\n    .spinner-block__small span{font-size:14px}\n    .spinner-block__medium{width:24px;height:24px}\n    .spinner-block__medium span{font-size:24px}\n    .spinner-block__high{width:42px;height:42px}\n    .spinner-block__high span{font-size:42px}\n    .spinner-block__wrapper span{animation:spinner .6s linear infinite}\n    #qr-code-container img{margin: auto}\n</style>\n<div class="modal fade" tabindex="-1" role="dialog" id="qr-modal" data-backdrop="static">\n    <div class="modal-dialog" role="document">\n        <form class="modal-content">\n            <div class="modal-body">\n                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n                <div class="text-center">\n                    <div class="spinner-block__wrapper spinner-block__container" id="qr-modal-spinner">\n                        <div class="spinner-block__wrapper spinner-block__high">\n                            <span class="fal fa-spinner-third"></span>\n                        </div>\n                    </div>\n\n                    <div class="center-block" id="qr-code-container"></div>\n                </div>\n            </div>\n            <div class="modal-footer">\n                <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal"><%= close_title %></button>\n            </div>\n        </form>\n    </div>\n</div>'), 
templates["addfunds/modal/qr_code_modal_field"] = _.template('<style>\n    @keyframes spinner{\n        to{transform:rotate(360deg)}\n    }\n    .spinner-block__inline{display:inline-block}\n    .spinner-block__container{display:block;width:100%;height:558px}\n    .spinner-block__wrapper{position:relative;display:flex;align-items:center;justify-content:center}\n    .spinner-block__small{width:16px;height:16px}\n    .spinner-block__small span{font-size:14px}\n    .spinner-block__medium{width:24px;height:24px}\n    .spinner-block__medium span{font-size:24px}\n    .spinner-block__high{width:42px;height:42px}\n    .spinner-block__high span{font-size:42px}\n    .spinner-block__wrapper span{animation:spinner .6s linear infinite}\n</style>\n<div class="modal fade" tabindex="-1" role="dialog" id="qr-modal" data-backdrop="static">\n    <div class="modal-dialog" role="document">\n        <form class="modal-content">\n            <div class="modal-body">\n                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n                <div class="text-center">\n                    <div class="spinner-block__wrapper spinner-block__container" id="qr-modal-spinner">\n                        <div class="spinner-block__wrapper spinner-block__high">\n                            <span class="fal fa-spinner-third"></span>\n                        </div>\n                    </div>\n                    <img alt="" class="img-responsive center-block m-auto" id="qr-code-image">\n                </div>\n\n                <p><%= instruction %></p>\n                <div class="form-group">\n                    <label for="qr-code-value" class="control-label"><%= code_field_title%></label>\n                    <textarea id="qr-code-value" readonly class="form-control">\n\n                        </textarea>\n                </div>\n                <button type="button" class="btn btn-primary" id="qr-code-copy-button"><%= copy_button_title%></button>\n            </div>\n            <div class="modal-footer">\n                <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal"><%= close_title %></button>\n            </div>\n        </form>\n    </div>\n</div>'), 
templates["addfunds/modal/shopinext_card"] = _.template('<div class="modal fade" id="shopinextCardModal" data-backdrop="static" tabindex="-1" role="dialog">\n    <div class="modal-dialog" role="document">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-label="<%= modal_title %>">\n                    <span aria-hidden="true">&times;</span>\n                </button>\n                <h4 class="modal-title"><%= modal_title %></h4>\n            </div>\n\n            <div class="modal-body">\n                <form action="" method="POST" id="shopinextCardForm">\n                    <div id="card-error-container" class="error-summary alert alert-danger hidden"></div>\n\n                    <div class="form-group">\n                        <label class="control-label" for="card-name"><%= card_name %></label>\n                        <input id="card-name" class="form-control" name="name" autocomplete="off" maxlength="32" />\n                    </div>\n                    <div class="form-group">\n                        <label class="control-label" for="card-number"><%= card_number %></label>\n                        <input id="card-number" class="form-control" name="number" autocomplete="off" maxlength="19" />\n                    </div>\n                    <div class="form-group">\n                        <label class="control-label" for="expiration-month"><%= expiry_month %></label>\n                        <input id="expiration-month" class="form-control" name="month" autocomplete="off" maxlength="2">\n                    </div>\n                    <div class="form-group">\n                        <label class="control-label" for="expiration-year"><%= expiry_year %></label>\n                        <input id="expiration-year" class="form-control" name="year" autocomplete="off" maxlength="4">\n                    </div>\n                    <div class="form-group">\n                        <label class="control-label" for="cvv"><%= cvv %></label>\n                        <input id="cvv" class="form-control" name="cvv" autocomplete="off" maxlength="4">\n                    </div>\n\n                    <div id="error"></div>\n\n                    <div class="modal-footer">\n                        <button type="submit" class="button-credit-card btn btn-primary btn-big-primary">\n                            <%= submit_title %>\n                        </button>\n                        <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal">\n                            <%= cancel_title %>\n                        </button>\n                    </div>\n\n                </form>\n            </div>\n\n        </div>\n    </div>\n</div>'), 
templates["addfunds/modal/squareup_card"] = _.template('<div class="modal fade" id="squareupCardModal" data-backdrop="static" tabindex="-1" role="dialog">\n    <div class="modal-dialog" role="document">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-label="<%= modal_title %>">\n                    <span aria-hidden="true">&times;</span>\n                </button>\n                <h4 class="modal-title"><%= modal_title %></h4>\n            </div>\n\n            <div id="form-container">\n                <div id="sq-ccbox">\n                    \x3c!--\n                      Be sure to replace the action attribute of the form with the path of\n                      the Transaction API charge endpoint URL you want to POST the nonce to\n                      (for example, "/process-card")\n                    --\x3e\n                    <form id="nonce-form" novalidate>\n                        <div class="modal-body">\n\n                            <div id="card-error-container" class="error-summary alert alert-danger hidden"></div>\n\n                            <fieldset>\n                                <div class="form-group">\n                                    <label class="control-label" for="sq-card-number"><%= card_number %></label>\n                                    <div id="sq-card-number" class="form-control"></div>\n                                </div>\n                                <div class="form-group">\n                                    <label class="control-label" for="sq-expiration-date"><%= expiration_date %></label>\n                                    <div id="sq-expiration-date" class="form-control"></div>\n                                </div>\n                                <div class="form-group">\n                                    <label class="control-label" for="sq-cvv"><%= cvv %></label>\n                                    <div id="sq-cvv" class="form-control"></div>\n                                </div>\n                                <div class="form-group">\n                                    <label class="control-label" for="sq-postal-code"><%= postal_code %></label>\n                                    <div id="sq-postal-code" class="form-control"></div>\n                                </div>\n                            </fieldset>\n\n                            <div id="error"></div>\n                            \x3c!--\n                              After a nonce is generated it will be assigned to this hidden input field.\n                            --\x3e\n                            <input type="hidden" id="card-nonce" name="nonce">\n                        </div>\n\n                        <div class="modal-footer">\n                            <button id="sq-creditcard" type="submit" class="button-credit-card btn btn-primary btn-big-primary">\n                                <%= submit_title %>\n                            </button>\n                            <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal">\n                                <%= cancel_title %>\n                            </button>\n                        </div>\n\n                    </form>\n                </div> \x3c!-- end #sq-ccbox --\x3e\n            </div> \x3c!-- end #form-container --\x3e\n\n        </div>\n    </div>\n</div>'), 
templates["addfunds/modal/stc_otp_token_modal"] = _.template('<div class="modal fade" id="STCPayOtpFormModal" data-backdrop="static" tabindex="-1" role="dialog">\n    <div class="modal-dialog" role="document">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-label="<%= close_title %>">\n                    <span aria-hidden="true">&times;</span>\n                </button>\n                <h4 class="modal-title"><%= modal_title %></h4>\n            </div>\n\n            <div class="modal-body">\n                <form action="" method="POST" id="STCPayOtpForm">\n                    <input type="hidden" name="otp_ref" value="">\n                    <input type="hidden" name="pmt_ref" value="">\n\n                    <div class="form-group">\n                        <label class="control-label" for="otp-token-field"><%= otp_title %></label>\n                        <input id="otp-token-field" class="form-control" name="otp_token" autocomplete="off" />\n                    </div>\n\n                    <div id="error"></div>\n\n                    <div class="modal-footer">\n                        <button type="submit" class="btn btn-primary btn-big-primary">\n                            <%= submit_title %>\n                        </button>\n                        <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal">\n                            <%= cancel_title %>\n                        </button>\n                    </div>\n\n                </form>\n            </div>\n\n        </div>\n    </div>\n</div>'), 
templates["addfunds/modal/sumup_card_widget"] = _.template('<div class="modal fade" id="sumupCardModal" data-backdrop="static" tabindex="-1" role="dialog">\n    <div class="modal-dialog" role="document">\n        <div class="modal-content">\n            <div class="modal-header">\n                <button type="button" class="close" data-dismiss="modal" aria-label="<%= modal_close %>">\n                    <span aria-hidden="true">&times;</span>\n                </button>\n                <h4 class="modal-title"><%= modal_title %></h4>\n            </div>\n\n            <div class="modal-body" id="sumup-card">\n            </div>\n\n        </div>\n    </div>\n</div>'), 
templates["addfunds/select"] = _.template('<div class="form-group fields" id="order_<%= name %>">\n    <label class="control-label" for="field-<%= name %>"><%= label %></label>\n    <select class="form-control" name="AddFoundsForm[fields][<%= name %>]" id="field-<%= name %>">\n        <% _.forEach(options, function(optLabel, optValue) {%>\n        <option value="<%= optValue %>" <% if (value == optValue) { %> selected <% } %>><%= optLabel %></option>\n        <%}); %>\n    </select>\n</div>'), 
templates["addfunds/stripe_card_element"] = _.template('<div class="form-group">\n    <label class="control-label" ><%= label %></label>\n    <div id="stripe-card-element" ></div>\n</div>'), 
templates["addfunds/stripe_iban_element"] = _.template('<div class="form-group" style="position: relative;">\n    <label class="control-label"><%= label %></label>\n    <div id="stripe-iban-element" class="form-control"></div>\n    <div id="stripe-iban-bank-name" style="position: absolute; right: 10px; margin-top: -30px; opacity: 0.8; z-index: 1;"></div>\n</div>'), 
templates["addfunds/stripe_payment_request_btn"] = _.template('<span id="<%= id %>" style="width: 150px; height: 18px; display: inline-block;" class="hidden"></span>'), 
templates["admin/integration_files"] = _.template('<% _.each(files, function(file) { %>\n<div class="row">\n    <div class="col-md-10">\n        <div class="form-group">\n            <label class="control-label" for="<%= file.file_name %>"><%= file.file_name %></label>\n            <% if(file.uploaded == true) { %>\n            <a href="/<%= file.file_name %>" target="_blank"><span class="fal fa-external-link"></span></a>\n            <% }; %>\n            <input type="hidden" name="EditIntegrationForm[files][]" value="<%= file.file_name %>">\n            <input type="file" id="<%= file.file_name %>" name="EditIntegrationForm[files][]" accept="<%= file.accept %>">\n        </div>\n    </div>\n</div>\n<% }); %>'), 
templates["admin/integration_settings_input"] = _.template('<div class="form-group">\n    <label for="edit-integration-settings-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <input class="form-control" value="<%= data[\'value\'] %>" name="EditIntegrationForm[settings][<%= data[\'name\'] %>]" id="edit-integration-settings-<%= data[\'code\'] %>">\n    <% if(data[\'help\']) { %>\n        <small class="help-block"><%= data[\'help\'] %></small>\n    <% }; %>\n</div>'), 
templates["admin/integration_settings_select"] = _.template('<div class="form-group">\n    <label for="edit-integration-settings-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <select class="form-control" name="EditIntegrationForm[settings][<%= data[\'name\'] %>]" id="edit-integration-settings-<%= data[\'code\'] %>">\n        <% _.forEach(data[\'options\'], function(label, value) {%>\n            <option value="<%= value %>" <% if (value == data[\'value\']) { %> selected <% } %>><%= label %></option>\n        <%}); %>\n    </select>\n</div>'), 
templates["admin/integration_snippet"] = _.template('<div class="form-group field-editintegrationform-code-<%= name %>">\n    <label class="control-label" for="editintegrationform-code-<%= name %>"><%= label %></label>\n    <textarea id="editintegrationform-code-<%= name %>" class="form-control" name="EditIntegrationForm[code][<%= name %>]" rows="7" placeholder=""><%= value %></textarea>\n</div>'), 
templates["admin/modules_modal_input"] = _.template('<div class="form-group">\n    <label class="control-label"><%= label %></label>\n    <input type="number" name="form_fields[<%= name %>]" class="form-control" value="<%= value %>">\n</div>'), 
templates["admin/modules_modal_select"] = _.template('<div class="form-group">\n    <label for="" class="control-label"><%= label %></label>\n    <select name="form_fields[<%= name %>]" class="form-control">\n        <% _.each(selectItems, function(item) { %>\n        <option <% if (item.value == value) { %> selected="selected" <% } %> value="<%= item.value %>"><%= item.label %></option>\n        <% }); %>\n    </select>\n</div>'), 
templates["admin/payment_settings_amounts"] = _.template('<div class="form-group__generator-row">\n    <span class="fas fa-times remove__generator-row"></span>\n    <div class="row">\n        <div class="col-md-4">\n            <div class="form-group">\n                <label for="edit-payment-method-options-amounts-<%= data[\'code\'][\'amount\'] %>" class="control-label"><%= data[\'label\'][\'amount\'] %></label>\n                <input type="number" class="form-control" name="EditPaymentMethodForm[options][amounts][<%= data[\'name\'][\'amount\'] %>][<% if (index) { %><%= index %><% } %>]" id="edit-payment-method-options-amounts-<%= data[\'code\'][\'amount\'] %>" value="<%= value[\'amount\'] %>">\n\n            </div>\n        </div>\n        <div class="col-md-8">\n            <div class="form-group">\n                <label for="edit-payment-method-options-amounts-<%= data[\'code\'][\'description\'] %>" class="control-label"><%= data[\'label\'][\'description\'] %></label>\n                <input type="text" class="form-control amount-description" name="EditPaymentMethodForm[options][amounts][<%= data[\'name\'][\'description\'] %>][<% if (index) { %><%= index %><% } %>]" id="edit-payment-method-options-amounts-<%= data[\'code\'][\'description\'] %>" value="<%= value[\'description\'] %>">\n            </div>\n        </div>\n    </div>\n</div>'), 
templates["admin/payment_settings_amounts_container"] = _.template("<div id=\"amount_container_row\" class=\"hidden\">\n    <div id=\"amount_container\">\n        <%= content %>\n    </div>\n    <a href=\"#\" class=\"form-group__generator-link add-amount\" data-id=\"#amount_container\" data-code='<%= JSON.stringify(data['code']) %>' data-name='<%= JSON.stringify(data['name']) %>' data-label='<%= JSON.stringify(data['label']) %>'><span><%= data['add_label'] %></span></a>\n</div>"), 
templates["admin/payment_settings_checkbox"] = _.template('<div class="form-check">\n    <div class="form-group__checkbox">\n        <label class="form-group__checkbox-label">\n            <input type="checkbox" class="form-check-input" name="EditPaymentMethodForm[options][<%= data[\'name\'] %>]" id="edit-payment-method-options-<%= data[\'code\'] %>" value="1" <% if (parseInt(data[\'value\'])) { %> checked<% } %>>\n            <input type="hidden" class="form-check-input" value=0 name="EditPaymentMethodForm[options][<%= data[\'name\'] %>]"  <% if (!parseInt(data[\'value\'])) { %> disabled<% } %>>\n            <span class="checkmark"></span>\n        </label>\n        <label for="edit-payment-method-options-<%= data[\'code\'] %>" class="form-group__label-title form-check-label">\n            <%= data[\'label\'] %>\n        </label>\n    </div>\n</div>'), 
templates["admin/payment_settings_commission_block"] = _.template('<label for="" class="control-label">Сommission</label>\n<div class="form-group__levels-row">\n    <div class="form-group__levels">\n        <label class="form-group__levels-label" for="editpaymentmethodform-pricefixed">Fixed (1.00)</label>\n        <input type="number" step="0.01" placeholder="0.00" value="<%= data.price_fixed %>" id="editpaymentmethodform-pricefixed"\n               name="EditPaymentMethodForm[priceFixed]">\n    </div>\n    <div class="form-group__levels-text">+</div>\n    <div class="form-group__levels">\n        <label class="form-group__levels-label" for="editpaymentmethodform-pricepercent">Percent (%)</label>\n        <input type="number" placeholder="0" value="<%= data.price_percent %>" id="editpaymentmethodform-pricepercent"\n               name="EditPaymentMethodForm[pricePercent]">\n    </div>\n</div>'), 
templates["admin/payment_settings_course"] = _.template('<div class="form-group">\n    <label for="edit-payment-method-options-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <input type="text" class="form-control" name="EditPaymentMethodForm[options][<%= data[\'name\'] %>]" id="edit-payment-method-options-<%= data[\'code\'] %>" value="<%= data[\'value\'] %>">\n</div>'), 
templates["admin/payment_settings_input"] = _.template('<div class="form-group">\n    <label for="edit-payment-method-options-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <input type="text" class="form-control" name="EditPaymentMethodForm[options][<%= data[\'name\'] %>]" id="edit-payment-method-options-<%= data[\'code\'] %>" value="<%= data[\'value\'] %>" <% if(data.disabled) { %> disabled <% } %>>\n</div>'), 
templates["admin/payment_settings_meta_tag_editable"] = _.template('<div class="form-group">\n    <label for="edit-payment-method-options-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <div class="input-group">\n\n        <input type="text"\n               class="form-control"\n               style="border-top-right-radius: 4px; border-bottom-right-radius: 4px;"\n               name="EditPaymentMethodForm[options][<%= data[\'name\'] %>]"\n               id="edit-payment-method-options-<%= data[\'code\'] %>"\n               value="<%= data[\'value\'] %>"\n               placeholder="<%= data[\'placeholder\'] %>"\n        <% if(data.value) { %> disabled <% } %>>\n\n        <div class="input-group-btn">\n            <div style="padding-left: 15px;">\n                <button type="button" class="btn btn-default" id="edit-payment-method-options-<%= data[\'code\'] %>-button"><%= Boolean(data[\'value\']) ? data[\'button_edit_title\'] : data[\'button_add_title\'] %></button>\n            </div>\n        </div>\n    </div>\n</div>'), 
templates["admin/payment_settings_multi_input"] = _.template('<div class="form-group form-group__paypal-description">\n    <span class="fas fa-times remove__paypal-description"></span>\n    <label for="edit-payment-method-options-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <input required type="text" class="form-control" name="EditPaymentMethodForm[options][<%= data[\'name\'] %>][]" id="edit-payment-method-options-<%= data[\'code\'] %>" value="<%= value %>">\n</div>'), 
templates["admin/payment_settings_multi_input_container"] = _.template('<div id="multi_input_container_<%= data[\'code\'] %>">\n    <%= content %>\n</div>\n<a href="#" class="add-multi-input" data-id="#multi_input_container_<%= data[\'code\'] %>" data-code="<%= data[\'code\'] %>" data-name="<%= data[\'name\'] %>" data-label="<%= data[\'label\'] %>"><span><%= data[\'add_label\'] %></span></a>'), 
templates["admin/payment_settings_select"] = _.template('<div class="form-group">\n    <label for="edit-payment-method-options-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <select class="form-control" name="EditPaymentMethodForm[options][<%= data[\'name\'] %>]" id="edit-payment-method-options-<%= data[\'code\'] %>">\n        <% _.forEach(data[\'options\'], function(label, value) {%>\n        <option value="<%= value %>" <% if (value == data[\'value\']) { %> selected <% } %>><%= label %></option>\n        <%}); %>\n    </select>\n</div>'), 
templates["admin/payment_settings_textarea"] = _.template('<div class="form-group">\n    <label for="edit-payment-method-options-<%= data[\'code\'] %>" class="control-label"><%= data[\'label\'] %></label>\n    <textarea cols="30" rows="10" class="form-control <%= data[\'options\'][\'class\'] %>" name="EditPaymentMethodForm[options][<%= data[\'name\'] %>]" id="edit-payment-method-options-<%= data[\'code\'] %>" <% if(data.disabled) { %> disabled <% } %>><%= data[\'value\'] %></textarea>\n</div>'), 
templates["admin/subscriptions_details"] = _.template('<% if (details.reason.content) { %>\n<div class="form-group">\n    <label for="reason"><%= details.reason.label %></label>\n    <input type="text" readonly class="form-control" id="reason" value="<%= details.reason.content %>">\n</div>\n<% } %>\n\n<% if (details.message.content) { %>\n<div class="form-group">\n    <label for=""><%= details.message.label %></label>\n    <input type="text" readonly class="form-control" id="" value="<%= details.message.content %>">\n</div>\n<% } %>'), 
templates["admin/subscriptions_error_details"] = _.template('<div class="form-group">\n    <label id="details_provider" for="details_details" class="control-label"><%= details.content_label %></label>\n    <textarea id="details_details" class="form-control" rows="7" readonly><%= details.content %></textarea>\n</div>'), 
templates["admin/subscriptions_fail_details"] = _.template('<div class="form-group">\n    <label id="details_provider" for="details_details" class="control-label"><%= details.content_label %></label>\n    <textarea id="details_details" class="form-control" rows="7" readonly><%= details.content %></textarea>\n</div>\n<div class="form-group">\n    <label for="details_code"><%= details.code_label %></label>\n    <input id="details_code" type="text" class="form-control" value="<%= details.code %>" readonly="">\n</div>'), 
templates["admin/users_activity_log_rows"] = _.template("<% _.each(rows, function(row) { %>\n<tr>\n    <td><%= row.ip %></td>\n    <td><%= row.date %></td>\n</tr>\n<% }); %>"), 
templates["modal/alert"] = _.template('<div class="modal fade confirm-modal" id="alertModal" aria-labelledby="myModalLabel" tabindex="-1" data-backdrop="static">\n    <div class="modal-dialog modal-sm modal-yesno" role="document">\n        <div class="modal-content">\n            <% if (typeof(alert_message) !== "undefined" && alert_message != \'\') { %>\n                <div class="modal-header">\n                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n                    <h4 class="modal-title m-t-0"><%= title %></h4>\n                </div>\n\n                <div class="modal-body">\n                    <p><%= alert_message %></p>\n                </div>\n            <% } else { %>\n                <div class="modal-header">\n                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\n                    <h4 class="modal-title m-t-0"><%= title %></h4>\n                </div>\n            <% } %>\n        </div>\n    </div>\n</div>'), 
templates["modal/confirm"] = _.template('<div class="modal fade confirm-modal" id="confirmModal" aria-labelledby="myModalLabel" tabindex="-1" data-backdrop="static">\n    <div class="modal-dialog modal-sm modal-yesno" role="document">\n        <div class="modal-content">\n            <% if (typeof(confirm_message) !== "undefined" && confirm_message != \'\') { %>\n            <div class="modal-body text-center">\n                <h5 class="mb-0"><%= title %></h5>\n            </div>\n\n            <div class="modal-body">\n                <p><%= confirm_message %></p>\n            </div>\n\n            <div class="modal-footer modal-footer__padding-10 justify-content-center">\n                <button class="btn btn-light btn-big-secondary" data-dismiss="modal" aria-hidden="true"><%= cancel_button %></button>\n                <button class="btn btn-primary btn-big-primary" id="confirm_yes"><%= confirm_button %></button>\n            </div>\n            <% } else { %>\n\n            <div class="modal-body">\n                <div class="m-b" align="center">\n                    <h4 class="m-t-0"> <%= title %></h4>\n                </div>\n\n                <div align="center">\n                    <button type="submit" class="btn btn-primary btn-big-primary" id="confirm_yes">\n                        <%= confirm_button %>\n                    </button>\n                    <button type="button" class="btn btn-default btn-big-secondary" data-dismiss="modal">\n                        <%= cancel_button %>\n                    </button>\n                </div>\n            </div>\n            <% } %>\n        </div>\n    </div>\n</div>'), 
templates["neworder/order_answer_number"] = _.template('<div class="form-group hidden fields" id="order_answer_number">\n    <label class="control-label" for="field-orderform-fields-answer_number"><%= label[\'answer_number\'] %></label>\n    <input class="form-control" name="OrderForm[answer_number]" value="<%= data[\'answer_number\'] %>" type="text" id="field-orderform-fields-answer_number"/>\n</div>'), 
templates["neworder/order_average_time"] = _.template('<div class="form-group hidden fields" id="order_average_time">\n    <label class="control-label" for="field-orderform-fields-average_time"><%= label[\'average_time\'] %>\n        <span class="ml-1 mr-1 fa fa-exclamation-circle" data-toggle="tooltip" data-placement="right"\n              title="<%= tooltips[\'average_time\'] %>">\n        </span>\n    </label>\n    <input class="form-control" readonly value="" type="text" id="field-orderform-fields-average_time"/>\n</div>'), 
templates["neworder/order_comment"] = _.template('<div class="form-group hidden fields" id="order_comment">\n    <label class="control-label" for="field-orderform-fields-comment"><%= label[\'comments\'] %></label>\n    <textarea class="form-control" name="OrderForm[comment]" id="field-orderform-fields-comment" cols="30" rows="10"><%= data[\'comment\'] %></textarea>\n</div>'), 
templates["neworder/order_comment_username"] = _.template('<div class="form-group hidden fields" id="order_comment_username">\n    <label class="control-label" for="field-orderform-fields-comment_username"><%= label[\'comment_username\'] %></label>\n    <input class="form-control" name="OrderForm[comment_username]" value="<%= data[\'username\'] %>" type="text" id="field-orderform-fields-comment_username"/>\n</div>'), 
templates["neworder/order_delay"] = _.template('<div class="form-group hidden fields" id="order_delay">\n    <div class="row">\n        <div class="col-md-6">\n            <label class="control-label" for="field-orderform-fields-delay"><%= label[\'delay\'] %></label>\n            <select class="form-control" name="OrderForm[delay]" id="field-orderform-fields-delay">\n                <% _.forEach(delays, function(delayLabel, delayValue) {%>\n                <option value="<%= delayValue %>" <% if (delayValue == data[\'delay\']) { %> selected <% } %>><%= delayLabel %></option>\n                <%}); %>\n            </select>\n        </div>\n        <div class="col-md-6">\n            <label for="field-orderform-fields-expiry"><%= label[\'expiry\'] %></label>\n            <div class="input-group">\n                <input class="form-control datetime" autocomplete="off" name="OrderForm[expiry]" value="<%= data[\'expiry\'] %>" type="text" id="field-orderform-fields-expiry"/>\n                <span class="input-group-btn">\n                    <button class="btn btn-default btn-big-secondary clear-datetime" type="button" data-rel="#field-orderform-fields-expiry"><span class="fa far fa-trash-alt"></span></button>\n                </span>\n            </div>\n        </div>\n    </div>\n</div>'), 
templates["neworder/order_dripfeed"] = _.template('<div id="dripfeed">\n    <div class="form-group fields hidden" id="order_check">\n        <div class="form-group__checkbox">\n            <label class="form-group__checkbox-label">\n                <input name="OrderForm[check]" value="1" type="checkbox" id="field-orderform-fields-check" <% if (data[\'check\']) { %> checked <% } %> />\n                <span class="checkmark"></span>\n            </label>\n            <label for="field-orderform-fields-check" class="form-group__label-title">\n                <%= label[\'dripfeed\'] %>\n            </label>\n        </div>\n        <div class="hidden depend-fields" id="dripfeed-options" data-depend="field-orderform-fields-check">\n            <div class="form-group">\n                <label class="control-label" for="field-orderform-fields-runs"><%= label[\'dripfeed.runs\'] %></label>\n                <input class="form-control" name="OrderForm[runs]" value="<%= data[\'runs\'] %>" type="text" id="field-orderform-fields-runs" />\n            </div>\n\n            <div class="form-group">\n                <label class="control-label" for="field-orderform-fields-interval"><%= label[\'dripfeed.interval\'] %></label>\n                <input class="form-control" name="OrderForm[interval]" value="<%= data[\'interval\'] %>" type="text" id="field-orderform-fields-interval" />\n            </div>\n\n            <div class="form-group">\n                <label class="control-label" for="field-orderform-fields-total-quantity"><%= label[\'dripfeed.total.quantity\'] %></label>\n                <input class="form-control" name="OrderForm[total_quantity]" value="<%= data[\'total_quantity\'] %>" type="text" id="field-orderform-fields-total-quantity" readonly=""/>\n            </div>\n        </div>\n    </div>\n</div>'), 
templates["neworder/order_email"] = _.template('<div class="form-group hidden fields" id="order_email">\n    <label class="control-label" for="field-orderform-fields-email"><%= label[\'email\'] %></label>\n    <input class="form-control" name="OrderForm[email]" value="<%= data[\'email\'] %>" type="text" id="field-orderform-fields-email"/>\n</div>'), 
templates["neworder/order_groups"] = _.template('<div class="form-group hidden fields" id="order_groups">\n    <label class="control-label" for="field-orderform-fields-groups"><%= label[\'groups\'] %></label>\n    <textarea class="form-control" name="OrderForm[groups]" id="field-orderform-fields-groups" cols="30" rows="10"><%= data[\'groups\'] %></textarea>\n</div>'), 
templates["neworder/order_hashtag"] = _.template('<div class="form-group hidden fields" id="order_hashtag">\n    <label class="control-label" for="field-orderform-fields-hashtag"><%= label[\'hashtag\'] %></label>\n    <input class="form-control" name="OrderForm[hashtag]" value="<%= data[\'hashtag\'] %>" type="text" id="field-orderform-fields-hashtag"/>\n</div>'), 
templates["neworder/order_hashtags"] = _.template('<div class="form-group hidden fields" id="order_hashtags">\n    <label class="control-label" for="field-orderform-fields-hashtags"><%= label[\'hashtags\'] %></label>\n    <textarea class="form-control" name="OrderForm[hashtags]" id="field-orderform-fields-hashtags" cols="30" rows="10"><%= data[\'hashtags\'] %></textarea>\n</div>'), 
templates["neworder/order_keywords"] = _.template('<div class="form-group hidden fields" id="order_keywords">\n    <label class="control-label" for="field-orderform-fields-keywords"><%= label[\'keywords\'] %></label>\n    <textarea class="form-control" name="OrderForm[keywords]" id="field-orderform-fields-keywords" cols="30" rows="10"><%= data[\'keywords\'] %></textarea>\n</div>'), 
templates["neworder/order_link"] = _.template('<div class="form-group hidden fields" id="order_link">\n    <label class="control-label" for="field-orderform-fields-link"><%= label[\'link\'] %></label>\n    <input class="form-control" name="OrderForm[link]" value="<%= data[\'link\'] %>" type="text" id="field-orderform-fields-link"/>\n</div>'), 
templates["neworder/order_media_url"] = _.template('<div class="form-group hidden fields" id="order_mediaUrl">\n    <label class="control-label" for="field-orderform-fields-mediaUrl"><%= label[\'mediaurl\'] %></label>\n    <input class="form-control" name="OrderForm[mediaUrl]" value="<%= data[\'mediaUrl\'] %>" type="text" id="field-orderform-fields-mediaUrl"/>\n</div>'), 
templates["neworder/order_mention_usernames"] = _.template('<div class="form-group hidden fields" id="order_mentionUsernames">\n    <label class="control-label" for="field-orderform-fields-mentionUsernames"><%= label[\'usernames\'] %></label>\n    <textarea class="form-control" name="OrderForm[mentionUsernames]" id="field-orderform-fields-mentionUsernames" cols="30" rows="10"><%= data[\'mentionUsernames\'] %></textarea>\n</div>'), 
templates["neworder/order_min"] = _.template('<div class="form-group hidden fields" id="order_min">\n    <label class="control-label" for="order_count"><%= label[\'quantity\'] %></label>\n    <div class="row">\n        <div class="col-md-6">\n            <input type="text" class="form-control" id="order_count" name="OrderForm[min]" value="<%= data[\'min\'] %>" placeholder="<%= label[\'min\'] %>" />\n        </div>\n\n        <div class="col-md-6">\n            <input type="text" class="form-control" id="order_count" name="OrderForm[max]" value="<%= data[\'max\'] %>" placeholder="<%= label[\'max\'] %>" />\n        </div>\n    </div>\n</div>'), 
templates["neworder/order_posts"] = _.template('<div class="form-group hidden fields" id="order_posts">\n    <label class="control-label" for="field-orderform-fields-posts"><%= label[\'new_posts\'] %></label>\n    <input class="form-control" name="OrderForm[posts]" value="<%= data[\'posts\'] %>" type="text" id="field-orderform-fields-posts"/>\n</div>'), 
templates["neworder/order_quantity"] = _.template('<div class="form-group hidden fields" id="order_quantity">\n    <label class="control-label" for="field-orderform-fields-quantity"><%= label[\'quantity\'] %></label>\n    <input class="form-control" name="OrderForm[quantity]" value="<%= data[\'quantity\'] %>" type="text" id="field-orderform-fields-quantity"/>\n</div>'), 
templates["neworder/order_user_name"] = _.template('<div class="form-group hidden fields" id="order_user_name">\n    <label class="control-label" for="field-orderform-fields-user_name"><%= label[\'username\'] %></label>\n    <input class="form-control w-full" name="OrderForm[user_name]" value="<%= data[\'user_name\'] %>" type="text" id="field-orderform-fields-user_name"/>\n</div>'), 
templates["neworder/order_username"] = _.template('<div class="form-group hidden fields" id="order_username">\n    <label class="control-label" for="field-orderform-fields-username"><%= label[\'username\'] %></label>\n    <input class="form-control" name="OrderForm[username]" value="<%= data[\'username\'] %>" type="text" id="field-orderform-fields-username"/>\n</div>'), 
templates["neworder/order_usernames"] = _.template('<div class="form-group hidden fields" id="order_usernames">\n    <label class="control-label" for="field-orderform-fields-usernames"><%= label[\'usernames\'] %></label>\n    <textarea class="form-control w-full" name="OrderForm[usernames]" id="field-orderform-fields-usernames" cols="30" rows="10"><%= data[\'usernames\'] %></textarea>\n</div>'), 
templates["neworder/order_usernames_custom"] = _.template('<div class="form-group hidden fields" id="order_usernames_custom">\n    <label class="control-label" for="field-orderform-fields-usernames_custom"><%= label[\'usernames\'] %></label>\n    <textarea class="form-control" name="OrderForm[usernames_custom]" id="field-orderform-fields-usernames_custom" cols="30" rows="10"><%= data[\'usernames_custom\'] %></textarea>\n</div>'), 
templates["user/user_info_modal"] = _.template('\x3c!-- Modal --\x3e\n<div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">\n    <div class="modal-dialog" role="document">\n        <form action="<%= action %>" id="userInfoForm" method="POST" class="modal-content">\n            <div class="modal-body">\n                <div id="userInfoError" class="error-summary alert alert-danger hidden"></div>\n                <div class="form-group">\n                    <label for="first_name"><%= labels[\'first_name\'] %></label>\n                    <input type="text" class="form-control" id="first_name" name="UpdateUserInfoFrom[first_name]" value="<%= values[\'first_name\'] %>">\n                </div>\n                <div class="form-group">\n                    <label for="last_name"><%= labels[\'last_name\'] %></label>\n                    <input type="text" class="form-control" id="last_name" name="UpdateUserInfoFrom[last_name]" value="<%= values[\'last_name\'] %>">\n                </div>\n\n                <input type="hidden" name="_csrf" value="<%= csrftoken %>">\n\n                <button type="submit" class="btn btn-primary btn-big-primary" id="userInfoSubmit"><%= labels[\'submit_btn\'] %></button>\n            </div>\n        </form>\n    </div>\n</div>'), 
customModule.layouts = {
run: function(e) {
$(document).on("click", ".alert .close", function(e) {
return e.preventDefault(), $(this).parents(".alert").hide(), !1;
}), $("form").submit(function(e) {
var t = $(this);
if (t.hasClass("submitted")) return e.preventDefault(), !1;
t.addClass("submitted");
}), e.auth && (window.localStorage && window.localStorage.setItem ? setTimeout(function e() {
var t = !localStorage.getItem("lastCheckAuth") && isNaN(localStorage.getItem("lastCheckAuth")) ? null : +new Date() - +localStorage.getItem("lastCheckAuth");
(6e4 <= t || null === t) && (localStorage.setItem("lastCheckAuth", +new Date()), 
$.get("/check-auth", function(e) {
e.isAuth || (localStorage.removeItem("lastCheckAuth"), window.location.href = window.location.href);
})), setTimeout(e, 6e4);
}, 6e4) : setTimeout(function e() {
$.get("/check-auth", function(e) {
e.isAuth || (window.location.href = window.location.href);
}), setTimeout(e, 6e4);
}, 6e4));
}
}, customModule.userInfoModal = {
run: function(e) {
this.init(e), $(document).on("click", "#userInfoSubmit", function(e) {
e.preventDefault();
var t = $(this), e = $("#userInfoForm"), a = $("#userInfoError", e);
return a.addClass("hidden"), console.log(e.serialize()), custom.sendFrom(t, e, {
data: e.serialize(),
callback: function(e) {
"success" == e.status && window.location.reload(), "error" == e.status && (a.removeClass("hidden"), 
a.html(e.error));
}
}), !1;
});
},
init: function(e) {
var t = templates["user/user_info_modal"];
$("body").append(t(e)), $("#userInfoModal").modal("show");
}
}, customModule.siteAddfunds = {
fieldsOptions: void 0,
amountOptions: void 0,
amountCurrencyOptions: void 0,
fieldsContainer: void 0,
run: function(e) {
var t = this;
t.fieldsContainer = $("form"), t.fieldOptions = e.fieldOptions, t.amountOptions = e.amountOptions, 
t.amountCurrencyOptions = e.amountCurrencyOptions, t.params = e;
try {
$('[data-toggle="tooltip"]').tooltip();
} catch (e) {
console.log("Error with tooltip. Catch", e);
}
t.updateAmountLabel(), $(document).on("change", "#method", function(e) {
e.preventDefault();
e = $(this).val();
t.updateFields(e), t.updateAmount(e), t.updateAmountCurrency(e);
}), void 0 !== e.options && (void 0 !== e.options.stripe && t.initStripe(e.options.stripe), 
void 0 !== e.options.bluesnap && t.initBlueSnap(e.options.bluesnap), void 0 !== e.options.razorpay && t.initRazorpay(e.options.razorpay), 
void 0 !== e.options.stripe3ds && t.initStripe3ds(e.options.stripe3ds), void 0 !== e.options.stripePay && t.initStripePay(e.options.stripePay), 
void 0 !== e.options.stripeAlipay && t.initStripeAlipay(e.options.stripeAlipay), 
void 0 !== e.options.stripeWeChatPay && t.initStripeWeChatPay(e.options.stripeWeChatPay), 
void 0 !== e.options.midtrans && t.initMidtrans(e.options.midtrans), void 0 !== e.options.paywithpaytm && t.initPaytm(e.options.paywithpaytm), 
void 0 !== e.options.paytmimap && t.initPaytm(e.options.paytmimap), void 0 !== e.options.klikbca && t.initKlikbca(e.options.klikbca), 
void 0 !== e.options.kasikornbank && t.initKlikbca(e.options.kasikornbank), void 0 !== e.options.authorize && t.initAuthorize(e.options.authorize), 
void 0 !== e.options.buypayer && t.initBuypayer(e.options.buypayer), void 0 !== e.options.qiwi && t.initQiwi(e.options.qiwi), 
void 0 !== e.options.payoneer && t.initTransactionImap(e.options.payoneer), void 0 !== e.options.mastercard && t.initMastercard(e.options.mastercard), 
void 0 !== e.options.mastercardEu && t.initMastercard(e.options.mastercardEu), void 0 !== e.options.squareup && t.initSquareup(e.options.squareup), 
void 0 !== e.options.checkout_com && t.initCheckoutCom(e.options.checkout_com), 
void 0 !== e.options.checkout_com_2 && t.initCheckoutCom(e.options.checkout_com_2), 
void 0 !== e.options.checkout_com_3ds && t.initCheckoutCom3Ds(e.options.checkout_com_3ds), 
void 0 !== e.options.manual_77 && t.initManual(e.options.manual_77), void 0 !== e.options.manual_243 && t.initManual(e.options.manual_243), 
void 0 !== e.options.manual_244 && t.initManual(e.options.manual_244), void 0 !== e.options.manual_245 && t.initManual(e.options.manual_245), 
void 0 !== e.options.manual_246 && t.initManual(e.options.manual_246), void 0 !== e.options.omise && t.initOmise(e.options.omise), 
void 0 !== e.options.paymes && t.initCard(e.options.paymes), void 0 !== e.options.stripeCheckout && t.initStripeCheckout(e.options.stripeCheckout), 
void 0 !== e.options.cryptochill && t.initCryptochill(e.options.cryptochill), void 0 !== e.options.pay2pay && t.initCard(e.options.pay2pay), 
void 0 !== e.options.phonepe && t.initPhonePe(e.options.phonepe), void 0 !== e.options.phonepeimap && t.initTransactionImap(e.options.phonepeimap), 
void 0 !== e.options.gbPrimePay && t.initQrModal(e.options.gbPrimePay), void 0 !== e.options.ksherBblPromptpay && t.initQrModal(e.options.ksherBblPromptpay), 
void 0 !== e.options.payiyo && t.initQrModal(e.options.payiyo), void 0 !== e.options.gbPrimePay3ds && t.initGbPrimePay3ds(e.options.gbPrimePay3ds), 
void 0 !== e.options.adyen && t.initAdyen(e.options.adyen), void 0 !== e.options.shopinext && t.initShopinext(e.options.shopinext), 
void 0 !== e.options.openmoney && t.initOpenMoney(e.options.openmoney), void 0 !== e.options.wechatpaynative && t.initWeChatPayNative(e.options.wechatpaynative), 
void 0 !== e.options.paghiper && t.initPaghiper(e.options.paghiper), void 0 !== e.options.sumup && t.initSumup(e.options.sumup), 
void 0 !== e.options.stcpay && t.initStcPay(e.options.stcpay), void 0 !== e.options.geidea && t.initGeidea(e.options.geidea), 
void 0 !== e.options.paymobkiosk && t.initPaymobKiosk(e.options.paymobkiosk)), $("#method").trigger("change");
},
copyFieldValue: function(e) {
e = document.getElementById(e);
e.select(), e.setSelectionRange(0, 99999), document.execCommand("copy");
},
updateFields: function(e) {
var a, n, o, i, r, t = this, l = t.params.options;
$("button[type=submit]", t.fieldsContainer).show(), $("#amount", t.fieldsContainer).parents(".form-group").show(), 
$(".fields", t.fieldsContainer).remove(), $("input,select", t.fieldsContainer).prop("disabled", !1), 
void 0 !== t.fieldOptions && void 0 !== t.fieldOptions[e] && t.fieldOptions[e] && (a = [], 
n = templates["addfunds/input"], o = templates["addfunds/hidden"], i = templates["addfunds/checkbox"], 
r = templates["addfunds/select"], $.each(t.fieldOptions[e], function(e, t) {
void 0 !== t && null != t && t && ("input" == t.type && a.push(n(t)), "hidden" == t.type && a.push(o(t)), 
"checkbox" == t.type && a.push(i(t)), "select" == t.type && a.push(r(t)));
}), l.stripeIndia && (l.stripeIndia.type == e ? t.initStripeIndia(l.stripeIndia) : t.closeStripeIndia()), 
$(".form-group", t.fieldsContainer).last().after(a.join("\r\n")), l.stripeSepa && (l.stripeSepa.type == e ? t.initStripeSepa(l.stripeSepa) : t.closeStripeSepa()));
},
updateAmount: function(e) {
var a, n, t = $("#amount"), o = t.val(), i = "amountSelect";
$("#" + i).remove(), t.length && (t.attr("step", "0.01"), t.removeClass("hidden"), 
void 0 !== this.amountOptions && void 0 !== this.amountOptions[e] && this.amountOptions[e] && (a = $("<select></select>").attr("id", i).attr("class", t.attr("class")).attr("name", t.attr("name")), 
t.after(a), t.addClass("hidden"), $.each(this.amountOptions[e], function(e, t) {
n = $("<option></option>").attr("value", e).text(t), o == t.id && n.attr("selected", "selected"), 
a.append(n);
})));
},
updateAmountLabel: function() {
var e, t, a = $("#amount_label_currency");
a.length || (e = $('label[for="amount"]', $("#amount").parents("form"))).length && (t = $("<span/>", {
id: "amount_label"
}).text(e.text()), a = $("<span/>", {
id: "amount_label_currency"
}), e.html("").append(t, " ", a));
},
updateAmountCurrency: function(e) {
var t = $("#amount_label_currency");
e && t.length && (void 0 !== this.amountCurrencyOptions && void 0 !== this.amountCurrencyOptions[e] && this.amountCurrencyOptions[e] ? t.text("(" + this.amountCurrencyOptions[e] + ")").removeClass("hidden") : t.text("").addClass("hidden"));
},
initWeChatPayNative: function(n) {
var o = this, i = function() {
$(".alert.alert-danger", o.fieldsContainer).remove();
};
$("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
$("body").append(templates["addfunds/modal/qr_code_modal_canvas"]({
close_title: n.modal.close_title
}));
var a = $("#qr-modal"), t = o.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: o.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
var t;
$("#qr-modal-spinner").hide(), "success" == e.status ? (a.modal("show"), a.on("hide.bs.modal", function() {
$("#qr-modal").remove(), window.location.reload();
}), t = $("#qr-code-container"), i(), new QRCode(t.get(0), e.data.code_url)) : "error" == e.status && (e = e.error && e.error.length ? e.error : n.defaultErrorText, 
i(), e && e.length && o.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
})));
}
}), !1;
});
},
initPaghiper: function(o) {
var i = this, r = function() {
$(".alert.alert-danger", i.fieldsContainer).remove();
};
$("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (o.type != t) return !0;
$("body").append(templates["addfunds/modal/qr_code_modal_field"]({
close_title: o.modal.close_title,
code_field_title: o.modal.code_field_title,
copy_button_title: o.modal.copy_button_title,
instruction: o.modal.instruction
}));
var n = $("#qr-modal"), t = i.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: i.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
var t, a;
$("#qr-modal-spinner").hide(), "success" == e.status ? (n.modal("show"), n.on("hide.bs.modal", function() {
$("#qr-modal").remove(), window.location.reload();
}), t = $("#qr-code-image"), a = $("#qr-code-value"), $("#qr-code-copy-button").on("click", function() {
i.copyFieldValue("qr-code-value");
}), a.val(e.data.emv), t.prop({
src: e.data.qrcode_image_url
}), t.prop({
alt: e.data.emv
}), r()) : "error" == e.status && (e = e.error && e.error.length ? e.error : o.defaultErrorText, 
r(), e && e.length && i.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
})));
}
}), !1;
});
},
initSumup: function(n) {
var o = this, i = function() {
$(".alert.alert-danger", o.fieldsContainer).remove();
};
$("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
$("body").append(templates["addfunds/modal/sumup_card_widget"]({
modal_title: n.modal.title,
modal_close: n.modal.close_title
}));
var a = $("#sumupCardModal"), t = o.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: o.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
$("#qr-modal-spinner").hide(), "success" == e.status ? (a.modal("show"), a.on("hide.bs.modal", function() {
$("#sumupCardModal").remove(), window.location.reload();
}), SumUpCard.mount({
checkoutId: e.data.checkoutId,
onResponse: function(e, t) {
window.location.reload();
}
}), i()) : "error" == e.status && (e = e.error && e.error.length ? e.error : n.defaultErrorText, 
i(), e && e.length && o.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
})));
}
}), !1;
});
},
initStcPay: function(n) {
function o(e) {
r(), e && e.length && i.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
}));
}
var i = this, r = (templates["addfunds/input"]({
name: "otp",
label: "OTP Token",
value: ""
}), function() {
$(".alert.alert-danger", i.fieldsContainer).remove();
});
$("button", i.fieldsContainer).on("click", function(e) {
if (n.type != $("#method").val()) return !0;
$("body").append(templates["addfunds/modal/stc_otp_token_modal"]({
close_title: n.modal.close_title,
submit_title: n.modal.submit_title,
cancel_title: n.modal.cancel_title,
modal_title: n.modal.title,
otp_title: n.modal.otp_title
}));
function a(e) {
function t() {
$(".alert.alert-danger", a).remove();
}
e.preventDefault();
var a = $(e.target);
return t(), $.ajax({
url: a.attr("action"),
data: $.param(a.serializeArray()),
async: !0,
method: "POST",
success: function(e) {
"Ok" === e ? window.location.reload() : (e = n.badTokenError, t(), e && e.length && a.prepend(templates["addfunds/alert"]({
text: e
})));
}
}), !1;
}
var t = i.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: i.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
var t;
"success" == e.status ? (t = $("#STCPayOtpFormModal"), $("[name=otp_ref]", t).val(e.data.otp_ref), 
$("[name=pmt_ref]", t).val(e.data.pmt_ref), $("form", t).attr("action", e.data.processing_url), 
$("form", t).on("submit", a), t.modal("show"), t.on("hide.bs.modal", function() {
$("#qr-modal").remove(), window.location.reload();
}), r()) : "error" == e.status && o(e.error && e.error.length ? e.error : n.defaultErrorText);
}
}), !1;
}), $("#STCPayOtpForm").on("submit", function(e) {
var t = e, e = $("#method").val();
if (n.type != e) return !0;
e = t.fieldsContainer.serializeArray();
return e.push({
name: "save",
value: 1
}), $.ajax({
url: t.fieldsContainer.attr("action"),
data: $.param(e),
async: !1,
method: "POST",
success: function(e) {
return "success" == e.status ? (window.location.replace("/add-funds"), r()) : "error" == e.status && o(e.error && e.error.length ? e.error : n.defaultErrorText), 
!1;
}
}), !1;
});
},
initBlueSnap: function(o) {
var i = this;
$("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (o.type != t) return !0;
t = $("#amount").val();
if (!t || isNaN(t)) return !0;
e.preventDefault();
var a = null, n = null;
return $.ajax({
url: i.fieldsContainer.attr("action"),
data: i.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = e.data.pfToken, n = e.data.transactionId);
}
}), !a || ($("#field-transaction_id").val(n), $("#field-token").val(a), e.preventDefault(), 
bluesnap.openCheckout({
token: a,
currency: o.currency,
description: o.description,
language: o.lang,
title: o.title,
amount: t
}, function(e) {
1 == e.code && $.ajax({
url: i.fieldsContainer.attr("action"),
data: i.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST"
}), window.location.reload();
}), !1);
});
},
initRazorpay: function(o) {
var i = this, r = function() {
$(".alert.alert-danger", i.fieldsContainer).remove();
};
$("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (o.type != t) return !0;
t = $("#amount").val();
if (!t || isNaN(t)) return !0;
e.preventDefault();
var a = null;
if ($.ajax({
url: i.fieldsContainer.attr("action"),
data: i.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST",
success: function(e) {
"success" == e.status ? (a = e.data, r()) : (e = e.error && e.error.length ? e.error : o.defaultErrorText, 
r(), e && e.length && i.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
})));
}
}), !a) return !0;
var n = a.options;
n.handler = function(e) {
document.getElementById("field-razorpay_payment_id").value = e.razorpay_payment_id, 
document.getElementById("field-razorpay_signature").value = e.razorpay_signature, 
document.getElementById("field-razorpay_order_id").value = n.order_id, document.getElementById("field-transaction_id").value = a.transactionId, 
i.fieldsContainer.submit();
}, n.theme.image_padding = !1, n.modal = {
ondismiss: function() {
console.log("This code runs when the popup is closed");
},
escape: !0,
backdropclose: !1
}, new Razorpay(n).open(), e.preventDefault();
});
},
initStripe: function(n) {
var o = this;
try {
var i = StripeCheckout.configure($.extend({}, !0, n.configure, {
token: function(e) {
$("#field-token").val(e.id), $("#field-email").val(e.email), o.fieldsContainer.submit();
}
}));
} catch (e) {
return void console.log("Something is wrong...", e);
}
$("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
var a = !1;
if ($.ajax({
url: o.fieldsContainer.attr("action"),
data: o.fieldsContainer.serialize(),
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = !0);
}
}), !a) return !0;
t = $.extend({}, !0, n.open);
return t.amount = 100 * $("#amount").val(), i.open(t), e.preventDefault(), !1;
}), $(window).on("popstate", function() {
i.close();
});
},
initStripe3ds: function(n) {
var o = this;
try {
var a = Stripe(n.configure.key);
} catch (e) {
return void console.log("Something is wrong...", e);
}
var i = null, r = StripeCheckout.configure($.extend({}, !0, n.configure, {
token: function(t) {
a.createSource({
type: "card",
token: t.id
}).then(function(e) {
!e.error && e.source || window.location.reload(), l(e.source, t);
});
}
})), l = function(e, t) {
"not_supported" !== e.card.three_d_secure ? a.createSource({
type: "three_d_secure",
amount: i,
currency: n.open.currency,
three_d_secure: {
card: e.id
},
redirect: {
return_url: n.auth_3ds_request.returnUrl + "&amount=" + i + "&method=" + n.type + "&token=" + t.id
}
}).then(function(e) {
e.error ? window.location.reload() : d(e.source, t);
}) : s(t.id, e.id);
}, d = function(e, t) {
window.location.replace(e.redirect.url);
}, s = function(e, t) {
$("#field-token").val(e), $("#field-source").val(t), o.fieldsContainer.submit();
};
if (/stripe3ds_auth_callback/.test(window.location.href)) {
var e = new URLSearchParams(window.location.search), t = e.get("method"), c = e.get("token"), m = e.get("source"), u = e.get("amount"), e = e.get("client_secret");
if (!(t && c && m && u && e)) return;
history.pushState({}, document.title, n.auth_3ds_request.errorUrl), $("#method").val(t).trigger("change"), 
$("#amount").val(u / 100), s(c, m);
}
$("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
var a = !1;
if (i = 100 * $("#amount").val(), $.ajax({
url: o.fieldsContainer.attr("action"),
data: o.fieldsContainer.serialize(),
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = !0);
}
}), !a) return !0;
t = $.extend({}, !0, n.open);
return t.amount = i, r.open(t), e.preventDefault(), !1;
}), $(window).on("popstate", function() {
r.close();
});
},
initStripeAlipay: function(n) {
var o = this, e = $("button", o.fieldsContainer);
try {
var i = Stripe(n.configure.key);
} catch (e) {
return void console.log("Something is wrong...", e);
}
e.on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
var a = null;
if ($(".alert.alert-danger", o.fieldsContainer).remove(), $.ajax({
url: o.fieldsContainer.attr("action"),
data: o.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = e.data.clientSecret);
}
}), !a) return !0;
e.preventDefault(), i.confirmAlipayPayment(a, {
return_url: n.configure.notify_url
}).then(function(e) {
e.error || location.reload();
});
});
},
initStripeWeChatPay: function(e) {
if (e.configure.cs && e.configure.key) {
var t = $("button[type=submit]", this.fieldsContainer);
t.prop("disabled", !0);
try {
new Stripe(e.configure.key).confirmWechatPayPayment(e.configure.cs, {
payment_method_options: {
wechat_pay: {
client: "web"
}
}
}).then(function(e) {
t.prop("disabled", !1), console.log(e);
});
} catch (e) {
console.log("Stripe WeChat Pay: error while obtaining the QR-code (" + e.message + ").");
}
}
},
initStripeIndia: function(n) {
var o = this, i = $("button", o.fieldsContainer);
try {
var r = Stripe(n.configure.key);
} catch (e) {
return void console.log("Something is wrong...", e);
}
var l = r.elements().create("card", {
style: {
base: {
color: "#32325d",
fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
fontSmoothing: "antialiased",
fontSize: "16px",
"::placeholder": {
color: "#aab7c4"
}
},
invalid: {
color: "#fa755a",
iconColor: "#fa755a"
}
}
}), e = templates["addfunds/stripe_card_element"]({
label: n.configure.cardLabel
});
$(e).insertAfter($(".form-group").eq(1)), l.mount("#stripe-card-element"), i.on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
e.preventDefault();
var a = null;
if ($.ajax({
url: o.fieldsContainer.attr("action"),
data: o.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = e.data.clientSecret);
}
}), !a) return !0;
i.data("secret", a), r.handleCardPayment(a, l, {
payment_method_data: {
billing_details: {
name: $("#field-name").val(),
address: {
line1: $("#field-line1").val(),
city: $("#field-city").val(),
state: $("#field-state").val(),
postal_code: $("#field-postal_code").val(),
country: $("#field-country").val()
}
}
}
}).then(function(e) {
e.error || location.reload();
});
});
},
closeStripeIndia: function() {
$("#stripe-card-element").parent().remove();
},
initStripePay: function(a) {
var t = this, n = $("button", t.fieldsContainer), o = "payment-request-button", e = templates["addfunds/stripe_payment_request_btn"];
n.after(e({
id: o
}));
var i = $("#" + o);
try {
var r = Stripe(a.configure.key);
} catch (e) {
return void console.log("Something is wrong...", e);
}
var l = r.paymentRequest(a.payment_request);
l.on("token", function(e) {
$("#field-token").val(e.token.id), $("#field-email").val(e.payerEmail), e.complete("success"), 
t.fieldsContainer.submit();
});
var d = r.elements().create("paymentRequestButton", {
paymentRequest: l,
style: {
paymentRequestButton: a.payment_request_button
}
});
l.canMakePayment().then(function(e) {
e && d.mount("#" + o);
}), $(document).on("change", "#method", function(e) {
var t = $(this).val();
i.addClass("hidden"), n.removeClass("hidden"), t == a.type && (n.addClass("hidden"), 
i.removeClass("hidden"));
}), $(document).on("change", "#amount", function(e) {
l.update({
total: {
label: a.payment_request.total.label,
amount: 100 * $("#amount").val()
}
});
});
},
initStripeSepa: function(a) {
var n = this;
$("#field-name").attr("required", !0).attr("minlength", "3"), $("#field-email").attr("required", !0).attr("type", "email");
try {
var o = Stripe(a.configure.key);
} catch (e) {
return void console.log("Something is wrong...", e);
}
$("#field-iban");
var i = o.elements().create("iban", {
style: {
base: {
color: "#32325d",
fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
fontSmoothing: "antialiased",
fontSize: "16px",
"::placeholder": {
color: "#aab7c4"
},
":-webkit-autofill": {
color: "#32325d"
}
},
invalid: {
color: "#fa755a",
iconColor: "#fa755a",
":-webkit-autofill": {
color: "#fa755a"
}
}
},
supportedCountries: [ "SEPA" ]
}), e = templates["addfunds/stripe_iban_element"]({
label: a.ibanLabel
});
$(e).insertAfter($(".form-group", n.fieldsContainer).last()), i.mount("#stripe-iban-element");
function r(e) {
l(), e && e.length && n.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
}));
}
var t = $("#stripe-iban-bank-name"), l = function() {
$(".alert.alert-danger", n.fieldsContainer).remove();
};
i.on("change", function(e) {
r(e.error ? e.error.message : ""), t.text(e.bankName || "");
}), n.fieldsContainer.on("submit", function(e) {
var t = $("#method").val();
if (a.type != t) return !0;
e.preventDefault();
e = {
type: "sepa_debit",
currency: "eur",
owner: {
name: $("#field-name").val(),
email: $("#field-email").val()
},
mandate: {
notification_method: "email"
}
};
o.createSource(i, e).then(function(e) {
e.error ? r(e.error.message) : (l(), $("#field-source", n.fieldsContainer).val(JSON.stringify(e.source)), 
custom.ajax({
url: n.fieldsContainer.attr("action"),
data: n.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST",
success: function(e) {
"success" == e.status ? window.location.reload() : "error" == e.status && r(e.error && e.error.length ? e.error : a.defaultErrorText);
},
error: function(e, t, a) {
t && "abort" === t.toLowerCase() || console.log("Something was wrong...", t, a, e);
}
}));
});
});
},
closeStripeSepa: function() {
$("#stripe-iban-element").parent().remove();
},
initMidtrans: function(o) {
var i = this;
$("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val(), a = $("#amount").val(), n = !1;
return o.type != t || parseInt(a, 10) != a || (snap.show(), $.ajax({
url: i.fieldsContainer.attr("action"),
data: i.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST",
success: function(e) {
"success" == e.status ? (e = e.data.token, snap.pay(e)) : (n = !0, snap.hide());
}
}), n ? void 0 : (e.preventDefault(), !1));
});
},
initPaytm: function(a) {
var n = this, o = templates["addfunds/input"], i = templates["addfunds/description"], r = $("#method", n.fieldsContainer).parents(".form-group"), e = $("#amount", n.fieldsContainer).parents(".form-group"), l = $("#amount_label", e), d = $("button[type=submit]", n.fieldsContainer), s = l.html(), c = d.html();
$(document).on("change", "#method", function(e) {
var t = $(this).val();
l.html(s), d.html(c), t == a.type && (e.stopImmediatePropagation(), $(".fields", n.fieldsContainer).remove(), 
e = [], n.fieldOptions[t].description && e.push(i($.extend({}, !0, n.fieldOptions[t].description, {
value: a.description
}))), n.fieldOptions[t].transaction_id && e.push(o(n.fieldOptions[t].transaction_id)), 
n.fieldOptions[t].amount && l.html(n.fieldOptions[t].amount.label), n.fieldOptions[t].submit && d.html(n.fieldOptions[t].submit.label), 
r.after(e.join("\r\n")));
});
},
initTransactionImap: function(a) {
var n = this, o = templates["addfunds/input"], i = templates["addfunds/description"], r = $("#method", n.fieldsContainer).parents(".form-group"), e = $("#amount", n.fieldsContainer).parents(".form-group"), l = $("#amount_label", e), d = l.html();
$(document).on("change", "#method", function(e) {
var t = $(this).val();
l.html(d), t == a.type && (e.stopImmediatePropagation(), $(".fields", n.fieldsContainer).remove(), 
e = [], n.fieldOptions[t].instruction && e.push(i($.extend({}, !0, n.fieldOptions[t].instruction, {
value: a.description
}))), n.fieldOptions[t].transaction_id && e.push(o(n.fieldOptions[t].transaction_id)), 
n.fieldOptions[t].amount && l.html(n.fieldOptions[t].amount.label), r.after(e.join("\r\n")));
});
},
initPaymobKiosk: function(a) {
var n = this, o = templates["addfunds/description"], i = $("#method", n.fieldsContainer).parents(".form-group");
$(document).on("change", "#method", function(e) {
var t = $(this).val();
t == a.type && (e.stopImmediatePropagation(), i.after(o($.extend({}, !0, n.fieldOptions[t].instruction, {
value: a.description
}))));
});
},
initQiwi: function(a) {
var n = this, o = templates["addfunds/input"], i = templates["addfunds/description"], r = $("#method", n.fieldsContainer).parents(".form-group"), e = $("#amount", n.fieldsContainer).parents(".form-group"), l = $("#amount_label", e), d = $("button[type=submit]", n.fieldsContainer), s = l.html(), c = d.html();
$(document).on("change", "#method", function(e) {
var t = $(this).val();
l.html(s), d.html(c), t == a.type && (e.stopImmediatePropagation(), $(".fields", n.fieldsContainer).remove(), 
e = [], n.fieldOptions[t].instruction && e.push(i($.extend({}, !0, n.fieldOptions[t].instruction, {
value: a.instruction
}))), n.fieldOptions[t].transaction_id && e.push(o(n.fieldOptions[t].transaction_id)), 
n.fieldOptions[t].amount && l.html(n.fieldOptions[t].amount.label), n.fieldOptions[t].submit && d.html(n.fieldOptions[t].submit.label), 
r.after(e.join("\r\n")));
});
},
initPhonePe: function(a) {
var n = this, o = templates["addfunds/description"], i = $("#method", n.fieldsContainer).parents(".form-group"), e = $("#amount", n.fieldsContainer).parents(".form-group"), r = $("button[type=submit]", n.fieldsContainer), l = $("input", e), d = $("select", i);
$(document).on("change", "#method", function(e) {
var t = $(this).val();
t == a.type && (e.stopImmediatePropagation(), e = [], n.fieldOptions[t].description && a.description && e.push(o($.extend({}, !0, n.fieldOptions[t].description, {
value: a.description
}))), a.amount && (l.val(a.amount), l.prop("disabled", !0), d.prop("disabled", !0), 
r.hide()), i.after(e.join("\r\n")));
});
},
initKlikbca: function(a) {
var n = this, o = templates["addfunds/description"], i = (templates["addfunds/disabled"], 
$("#method", n.fieldsContainer).parents(".form-group")), e = $("#amount", n.fieldsContainer).parents(".form-group"), r = $("button[type=submit]", n.fieldsContainer), l = $("input", e), d = $("select", i);
$(document).on("change", "#method", function(e) {
var t = $(this).val();
t == a.type && (e.stopImmediatePropagation(), e = [], n.fieldOptions[t].description && a.description && e.push(o($.extend({}, !0, n.fieldOptions[t].description, {
value: a.description
}))), a.amount && (l.val(a.amount), l.prop("disabled", !0), d.prop("disabled", !0), 
r.hide()), i.after(e.join("\r\n")));
});
},
initAuthorize: function(t) {
var e = t.configure, a = $("#amount"), n = $("#method"), o = $("button[type=submit]", this.fieldsContainer), i = $("<button />", e).hide();
o.after(i), o.on("click", function(e) {
if (n.val() == t.type && 0 < +$.trim(a.val())) return e.stopImmediatePropagation(), 
i.trigger("click"), !1;
});
},
responseAuthorizeHandler: function(e) {
if ("Error" === e.messages.resultCode) for (var t = 0; t < e.messages.message.length; ) alert(e.messages.message[t].code + ": " + e.messages.message[t].text), 
t += 1; else $("#field-data_descriptor").val(e.opaqueData.dataDescriptor), $("#field-data_value").val(e.opaqueData.dataValue), 
$("form").submit();
},
initBuypayer: function(e) {
e.content && window.open(e.content, "_top");
},
initMastercard: function(i) {
var r = this;
$("button", r.fieldsContainer).on("click", function(e) {
$(".alert").remove();
var t = $("#method").val();
if (i.type != t) return !0;
var a = !1, n = null, o = null, t = r.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: r.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = !0, n = e.data.payment_id, o = e.data.session_id);
}
}), !a || (i.configure.order.amount = function() {
return $("#amount").val();
}, i.configure.order.id = n, i.configure.session.id = o, i.configure.callback.error = function(e) {
console.log(JSON.stringify(e));
}, i.configure.callback.cancel = function(e) {
console.log("Payment cancelled");
}, Checkout.configure(i.configure), Checkout.showLightbox(), !1);
});
},
initSquareup: function(n) {
var t, o = this, a = n.applicationId || null, i = n.locationId || null;
$("body").append(templates["addfunds/modal/squareup_card"]({
modal_title: n.modal.modal_title,
submit_title: n.modal.submit_title,
cancel_title: n.modal.cancel_title,
card_number: n.modal.card_number,
cvv: n.modal.cvv,
expiration_date: n.modal.expiration_date,
postal_code: n.modal.postal_code
}));
var r = $("#squareupCardModal"), e = $("#nonce-form"), l = $("#card-error-container");
$("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
var a = !1;
return $.ajax({
url: o.fieldsContainer.attr("action"),
data: o.fieldsContainer.serialize(),
async: !1,
method: "POST",
success: function(e) {
"success" === e.status && (a = !0);
}
}), !a || (r.modal("show"), e.preventDefault(), !1);
}), r.on("show.bs.modal", function(e) {
if (!SqPaymentForm.isSupportedBrowser()) throw "Browser not supported!";
(t = new SqPaymentForm({
applicationId: a,
locationId: i,
inputClass: "sq-input",
autoBuild: !1,
applePay: !1,
masterpass: !1,
callbacks: {
createPaymentRequest: function() {
return {
requestShippingAddress: !1,
requestBillingInfo: !1,
currencyCode: "USD",
countryCode: "US",
total: {
label: "MERCHANT NAME",
amount: "100",
pending: !1
},
lineItems: [ {
label: "Subtotal",
amount: "100",
pending: !1
} ]
};
},
cardNonceResponseReceived: function(e, t, a) {
l.toggleClass("hidden", !e || !!t), e ? _.isArray(e) && e[0].hasOwnProperty("message") && l.html(e[0].message) : t ? ($("#field-card-nonce").val(t), 
r.modal("hide"), o.fieldsContainer.submit()) : l.html(n.modal.default_card_error);
},
unsupportedBrowserDetected: function() {},
inputEventReceived: function(e) {
e.eventType;
},
paymentFormLoaded: function() {
console.log("The form loaded!");
}
}
})).build(), t.recalculateSize();
}), r.on("hide.bs.modal", function(e) {
if (l.html(""), l.addClass("hidden"), !t) throw "No payment form!";
t.destroy(), t = null;
}), e.on("submit", function(e) {
if (e.preventDefault(), !t) throw "No payment form!";
t.requestCardNonce();
});
},
initCheckoutCom: function(n) {
$("body").append(templates["addfunds/modal/checkout_com_card"]({
modal_title: n.modal.modal_title,
submit_title: n.modal.submit_title,
cancel_title: n.modal.cancel_title
}));
var o = this, i = $("#checkoutcomCardModal"), t = $("form", i), e = $(":submit", i);
e.attr("disabled", !0);
var r = {
publicKey: n.public_key,
containerSelector: ".frames-container",
cardValidationChanged: function() {
e.attr("disabled", !Frames.isCardValid());
},
cardSubmitted: function() {
e.attr("disabled", !0);
}
};
t.on("submit", function(e) {
e.preventDefault(), Frames.submitCard().then(function(e) {
$("#field-card-token").val(e.cardToken), i.modal("hide"), o.fieldsContainer.submit();
}).catch(function(e) {
console.log(e);
});
}), $("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val(), a = !1;
return n.type != t || ($.ajax({
url: o.fieldsContainer.attr("action"),
data: o.fieldsContainer.serialize(),
async: !1,
method: "POST",
success: function(e) {
"success" === e.status && (a = !0);
}
}), !a || (Frames.init(r), i.modal("show"), e.preventDefault(), !1));
}), i.on("show.bs.modal", function(e) {
$("input", t).val("");
});
},
initCheckoutCom3Ds: function(n) {
var o, i = this;
$("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val(), a = !1;
return n.type != t || ($.ajax({
url: i.fieldsContainer.attr("action"),
data: i.fieldsContainer.serialize(),
async: !1,
method: "POST",
success: function(e) {
"success" === e.status && (a = !0);
}
}), !a || (o = $.extend({}, n.init_options, {
value: 100 * $("#amount").val(),
customerName: $("#field-billing_name").val(),
billingDetails: {
addressLine1: $("#field-billing_line_1").val(),
addressLine2: "",
postcode: $("#field-billing_postal_code").val(),
country: $("#field-billing_country_code").val(),
city: $("#field-billing_city").val(),
phone: {
number: $("#field-billing_phone").val()
}
},
cardTokenised: function(e) {
$("#field-card-token").val(e.data.cardToken), i.fieldsContainer.submit();
}
}), Checkout.configure(o), Checkout.open(), e.preventDefault(), !1));
});
},
initManual: function(n) {
var o = this, i = templates["addfunds/description"], r = $("#method", o.fieldsContainer).parents(".form-group"), l = $("#amount", o.fieldsContainer).parents(".form-group"), d = $("button[type=submit]", o.fieldsContainer);
$(document).on("change", "#method", function(e) {
var t = $(this).val(), a = [];
t == n.type && (e.stopImmediatePropagation(), d.hide(), l.hide(), o.fieldOptions[t].instruction && a.push(i($.extend({}, !0, o.fieldOptions[t].instruction, {
value: n.instruction
}))), r.after(a.join("\r\n")));
});
},
initOmise: function(n) {
var t, o = this;
$(document).on("change", "#method", function(e) {
$(this).val() == n.type && ((t = $.extend({}, !0, n.config)).submitFormTarget = "#form_id", 
t.onCreateTokenSuccess = i, t.onFormClosed = a, OmiseCard.configure(t));
});
var a = function() {}, i = function(e) {
$("#field-card-token").val(e), o.fieldsContainer.submit();
};
$("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
var a = !1;
return $.ajax({
url: o.fieldsContainer.attr("action"),
data: o.fieldsContainer.serialize(),
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = !0);
}
}), !a || (OmiseCard.open({
amount: 100 * $("#amount").val()
}), e.preventDefault(), !1);
});
},
initCard: function(a) {
var n = $("#amount", this.fieldsContainer).parents(".form-group"), o = templates["addfunds/custom/credit_card"];
$(document).on("change", "#method", function(e) {
var t = [];
$(this).val() == a.type && (t.push(o($.extend({}, !0, a.card_fields))), n.after(t.join("\r\n")));
}), $("button", this.fieldsContainer).on("click", function(e) {
_.each(a.card_fields, function(e) {
$("#field-" + e.name).val($("#field-visible-" + e.name).val());
});
});
},
initStripeCheckout: function(o) {
var i = this;
try {
var r = Stripe(o.configure.public_key);
} catch (e) {
return void console.log("Something is wrong...", e);
}
$("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (o.type != t) return !0;
var a = !1, n = null, t = i.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: i.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = !0, n = e.data.session_id);
}
}), !a || (r.redirectToCheckout({
sessionId: n
}).then(function(e) {
console.log("Something is wrong...", e);
}), !1);
});
},
initPay2Pay: function(a) {
var n = $("#amount", this.fieldsContainer).parents(".form-group"), o = templates["addfunds/custom/credit_card"];
$(document).on("change", "#method", function(e) {
var t = [];
$(this).val() == a.type && (t.push(o($.extend({}, !0, a.card_fields))), n.after(t.join("\r\n")));
}), $("button", this.fieldsContainer).on("click", function(e) {
_.each(a.card_fields, function(e) {
$("#field-" + e.name).val($("#field-visible-" + e.name).val());
});
});
},
initQrModal: function(o) {
var i = this, r = function() {
$(".alert.alert-danger", i.fieldsContainer).remove();
};
$("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (o.type != t) return !0;
var a, n;
$("body").append(templates["addfunds/modal/qr_code_modal"]({
close_title: o.modal.close_title
}));
t = $("#qr-modal");
t.modal("show"), t.on("hide.bs.modal", function() {
$("#qr-modal").remove(), window.location.reload();
});
t = i.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: i.fieldsContainer.attr("action"),
data: $.param(t),
method: "POST",
success: function(e) {
var t;
$("#qr-modal-spinner").hide(), "success" == e.status ? (t = $("#qr-code-image"), 
r(), a = e.data.qr_code, n = "data:image/png;base64," + a, t.prop("src", n)) : "error" == e.status && (e = e.error && e.error.length ? e.error : o.defaultErrorText, 
r(), e && e.length && i.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
})));
}
}), !1;
});
},
initGbPrimePay3ds: function(a) {
$("body").append(templates["addfunds/modal/gb_prime_pay_3ds"]({
modal_title: a.modal.modal_title,
close_title: a.modal.close_title
}));
var n = $("#gbPrimePay3dsCardModal"), o = $("#amount", this.fieldsContainer), e = $("#gb-form"), t = $("#gb-modal-spinner");
e.on("DOMSubtreeModified", function() {
t.hide();
}), n.on("hide.bs.modal", function() {
window.location.reload();
}), $("button", this.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
return a.type != t || (new GBPrimePay({
publicKey: a.public_key,
gbForm: "#gb-form",
merchantForm: "form",
amount: o.val(),
env: 0 == a.test_mode ? "prd" : "test"
}), n.modal("show"), e.preventDefault(), !1);
});
},
initAdyen: function(n) {
var o, e, i = this;
n.paymentMethods && "object" == typeof n.paymentMethods && !$.isEmptyObject(n.paymentMethods) && ($("body").append(templates["addfunds/modal/adyen_modal"]({
modal_title: n.modal.modal_title,
close_title: n.modal.close_title
})), o = $("#adyenModal"), $("#amount", i.fieldsContainer), o.on("hide.bs.modal", function() {
window.location.reload();
}), e = {
paymentMethodsResponse: n.paymentMethods,
clientKey: n.clientKey,
locale: "en-US",
environment: n.environment,
onSubmit: function(e, t) {
$("#field-state").val(JSON.stringify(e.data)), $.ajax({
url: i.fieldsContainer.attr("action"),
data: i.fieldsContainer.serialize() + "&save=true",
async: !1,
method: "POST",
success: function(e) {
"success" == e.status ? e.data.action ? t.handleAction(e.data.action) : window.location.reload() : "error" == e.status && showError(e.error && e.error.length ? e.error : n.defaultErrorText);
}
});
},
card: {
hasHolderName: !0,
holderNameRequired: !0,
enableStoreDetails: !0,
hideCVC: !1,
name: "Credit or debit card"
}
}, new AdyenCheckout(e).create("dropin").mount("#dropin-container"), $("button", i.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
var a = !1;
return $.ajax({
url: i.fieldsContainer.attr("action"),
data: i.fieldsContainer.serialize(),
async: !1,
method: "POST",
success: function(e) {
"success" == e.status && (a = !0);
}
}), !a || (o.modal("show"), e.preventDefault(), !1);
}));
},
initShopinext: function(r) {
var l = this;
$("button", l.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (r.type != t) return !0;
$("body").append(templates["addfunds/modal/shopinext_card"]({
modal_title: r.modal.modal_title,
submit_title: r.modal.submit_title,
cancel_title: r.modal.cancel_title,
card_name: r.modal.card_name,
card_number: r.modal.card_number,
cvv: r.modal.cvv,
expiry_month: r.modal.expiry_month,
expiry_year: r.modal.expiry_year
}));
var a = $("#shopinextCardModal"), n = $("#shopinextCardForm");
$("#card-error-container");
a.on("hide.bs.modal", function() {
window.location.reload();
}), $("#card-number", a).mask("0000 0000 0000 0000"), $("#expiration-month", a).mask("00"), 
$("#expiration-year", a).mask("0000"), $("#cvv", a).mask("0000");
var o = !1, i = "", t = l.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: l.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
"success" === e.status && (o = !0, i = e.data.action);
}
}), !o || (a.modal("show"), n.attr("action", i), e.preventDefault(), !1);
});
},
initCryptochill: function(a) {
var n = this, o = "cryptochill_payment_request_btn", i = templates["addfunds/cryptochill_payment_request_btn"];
function r(e, t) {
$("#" + o).remove();
}
$("button", n.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (a.type != t) return !0;
t = n.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: n.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
if ("success" == e.status) return $(n.fieldsContainer).after(i({
id: o,
amount: e.data.amount,
product: e.data.product,
passthrough: e.data.passthrough,
currency: e.data.currency
})), CryptoChill.setup({
account: a.configure.account_id,
profile: a.configure.profile_id,
onOpen: r
}), !1;
}
}), $("#" + o).click(), !1;
});
},
initGeidea: function(o) {
function i(e) {
n(), e && e.length && a.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e
}));
}
var a = this, n = function() {
$(".alert.alert-danger", a.fieldsContainer).remove();
};
$("button", a.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (o.type != t) return !0;
n();
t = a.fieldsContainer.serializeArray();
return t.push({
name: "save",
value: 1
}), $.ajax({
url: a.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
if ("success" == e.status) {
var t = function() {
window.location.reload();
}, a = function(e) {
i(e.responseMessage);
}, n = function() {
window.location.reload();
}, n = new GeideaApi(e.data.merchant_key, t, a, n);
return n.configurePayment(e.data.payload), n.startPayment(), console.log(e.data.payload), 
!1;
}
if ("error" == e.status) return i(e.error && e.error.length ? e.error : o.defaultErrorText), 
!1;
}
}), !1;
});
},
initOpenMoney: function(n) {
var o = this;
$("button", o.fieldsContainer).on("click", function(e) {
var t = $("#method").val();
if (n.type != t) return !0;
var a = null, t = o.fieldsContainer.serializeArray();
t.push({
name: "save",
value: 1
}), $(".alert.alert-danger", o.fieldsContainer).remove(), $.ajax({
url: o.fieldsContainer.attr("action"),
data: $.param(t),
async: !1,
method: "POST",
success: function(e) {
"success" === e.status ? a = e.data.token : o.fieldsContainer.prepend(templates["addfunds/alert"]({
text: e.error
}));
}
}), e.preventDefault(), a ? Layer.checkout({
token: a,
accesskey: n.key
}, function(e) {
"captured" == e.status || "created" == e.status || "pending" == e.status || "failed" == e.status || e.status, 
console.log("Layer response:", e), window.location.reload();
}, function(e) {
console.log("Layer error:", e);
}) : console.log("Token is empty");
});
}
};

var responseAuthorizeHandler = customModule.siteAddfunds.responseAuthorizeHandler;

customModule.api = {
run: function(e) {
$("#service_type").length || $('div[id^="type_"]').show(), $("#service_type").change(function() {
$("div[id^='type_']").hide();
var e = $("#service_type").val();
$("#type_" + e).show();
}), $("#service_type").trigger("change");
}
}, customModule.siteOrder = {
run: function(e) {
document.forms.sendform.submit();
}
}, customModule.confirmEmail = {
run: function(e) {
var t = $("#changeEmailModal"), a = $("#changeEmailForm");
a.attr("action", e.change_email_url), $("#changeEmailLink").on("click", function(e) {
return e.preventDefault(), $("#new-email, #current-password", a).val(""), t.modal("show"), 
!1;
}), a.on("submit", function(e) {
e.preventDefault();
e = $("#changeEmailSubmitBtn", a);
return custom.sendFrom(e, a, {
data: a.serialize(),
callback: function() {
location.reload();
}
}), !1;
});
}
}, customModule.siteHistory = {
run: function(e) {
$("#setRefill").on("show.bs.modal", function(e) {
$("#refill_loader").show(), $("#refill_body").html("");
var t = $(this), t = $("form", t);
$('input[name="id"]', t).val($(e.relatedTarget).data("href")), $.post(t.attr("action"), t.serialize(), function(e) {
return "success" == e.status ? (location.reload(), !1) : void ("error" == e.status && ($("#refill_loader").hide(), 
$("#refill_body").html(e.error)));
});
});
}
}, customModule.siteOrder = {
fieldsOptions: void 0,
fieldsContainer: void 0,
services: [],
fields: [],
maxQuantity: 0,
run: function(e) {
var t, n = this;
n.services = $.extend({}, !0, e.services), n.fields = $.extend({}, !0, e.fields), 
n.currencyOptions = $.extend({}, !0, e.currency), n.format = $.extend({}, !0, e.format), 
n.fieldsContainer = $("#fields"), n.fieldOptions = e.fieldOptions, $(document).on("change", "#orderform-category", function() {
var e = $(this).val();
n.updateServices(e), $("#orderform-service").trigger("change");
}), $(document).on("change", "#orderform-service", function() {
var e = $(this), t = $("option:selected", e).data("type"), e = $("option:selected", e).val(), e = n.services[e].link_type;
n.updateFields(t, !0, e), n.updateDescription(), n.updateQuantityHelpBlock(), n.updateAverageTime();
}), $(document).on("keyup", ".counter", function() {
var e = $(this), t = $("input, textarea", $("#order_" + e.data("related") + ".fields")), a = 0;
t.length && $.each(e.val().split("\n"), function(e, t) {
0 < $.trim(t).length && a++;
}), t.val(a), n.updateCharge();
}), $(document).on("change", "#field-orderform-fields-check", function() {
var e = $(this), t = e.attr("id"), t = $('.depend-fields[data-depend="' + t + '"]');
e.prop("checked") ? t.removeClass("hidden") : t.addClass("hidden"), n.updateTotalQuantity();
}), $(document).on("keyup", "#field-orderform-fields-quantity", function() {
n.updateCharge();
}), $(document).on("keyup", "#field-orderform-fields-quantity, #field-orderform-fields-runs", function() {
n.updateTotalQuantity();
}), $(document).on("click", ".clear-datetime", function() {
var e = $(this).data("rel");
$(e).val("");
}), $("#orderform-category").length ? $("#orderform-category").trigger("change") : n.updateServices(), 
n.initFields(), t = $("#orderform-service"), e = $("option:selected", t).data("type"), 
t = $("#orderform-service").val(), n.updateFields(e, !1, n.services[t].link_type), 
n.updateCharge(), n.updateDescription(), n.updateAverageTime(), n.updateQuantityHelpBlock(), 
$("#field-orderform-fields-check").trigger("change");
},
getMaxQuantityValue: function() {
var e = this.getCurrentServicePrice();
return e <= 0 ? Number.MAX_SAFE_INTEGER : parseInt(Number.MAX_SAFE_INTEGER - 1 / e);
},
preparePrice: function(e) {
var t = this.format, a = t.min, n = (e = $.trim(e.toString().replace(",", "."))).split(".");
return void 0 !== n[1] && (n[1] = n[1].replace(/0+$/g, ""), n[1].length > a && (a = t.max < n[1].length ? t.max : n[1].length)), 
e = (e = 1e3 <= (e = (e = parseFloat(e)).toFixed(a)) ? e.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, "$1" + t.thousands) : e).replace(/\.(\d+)$/g, t.delimiter + "$1"), 
e = this.currencyOptions.template.replace("{{value}}", e);
},
getCurrentServicePrice: function() {
var e = $("#orderform-service"), e = this.services[e.val()];
if (!e) return 0;
e = e.orig_price;
return parseFloat(e);
},
getCurrentServiceType: function() {
var e = $("#orderform-service"), e = this.services[e.val()];
return e ? e.type : 0;
},
updateTotalQuantity: function() {
var e = $("#field-orderform-fields-quantity"), t = $("#field-orderform-fields-runs"), a = $("#field-orderform-fields-total-quantity"), e = +e.val(), t = +t.val();
a.val(e * t), this.updateCharge();
},
updateCharge: function() {
var e, t = this, a = $("#charge"), n = a.parent(".form-group"), o = $("#field-orderform-fields-quantity"), i = $("#orderform-service"), r = t.getMaxQuantityValue();
n.show(), a.val(t.preparePrice(0)), t.services[i.val()] && (e = t.getCurrentServicePrice(), 
10 != (i = +t.getCurrentServiceType()) && 14 != i && 16 != i ? 100 != i ? (12 == i && $("#field-orderform-fields-check").prop("checked") && (o = $("#field-orderform-fields-total-quantity")), 
(o = $.trim(o.val())) && "" != o && o.length && !isNaN(o) && o.match(/^\d+$/gi) ? (o = parseInt(o), 
isNaN(o) ? a.val("") : (e *= o = r < o ? r : o, a.val(t.preparePrice(e /= 1e3)))) : a.val("")) : n.hide() : a.val(t.preparePrice(e)));
},
updateServices: function(a) {
var e = this, n = $("#orderform-service"), t = n.parent(".form-group"), o = [], i = void 0;
void 0 !== e.fieldOptions && void 0 !== e.fieldOptions.data && void 0 !== e.fieldOptions.data.service && (i = e.fieldOptions.data.service), 
t.addClass("hidden"), n.html(""), $.each(e.services, function(e, t) {
void 0 !== a && a != t.cid || o.push(t);
}), o.sort(function(e, t) {
e = parseInt(e.position), t = parseInt(t.position);
return e < t ? -1 : t < e ? 1 : 0;
});
var r = void 0;
$.each(o, function(e, t) {
r = $("<option></option>").attr("data-type", t.type).attr("value", t.id).text(t.name), 
i == t.id && r.attr("selected", "selected"), n.append(r);
}), $("option", n).length && t.removeClass("hidden");
},
updateFields: function(e, t, i) {
var r = this, a = $('.fields input[type="text"], .fields textarea, .depend-fields input[type="text"], .depend-fields textarea');
if (a.prop("disabled", !1), a.removeAttr("data-related"), a.removeClass("counter"), 
$(".fields, .depend-fields").addClass("hidden"), void 0 !== r.fields[e]) {
$.each(r.fields[e], function(e, t) {
var a = $("#order_" + t.name + ".fields"), n = $("input, textarea", a), o = $("label", a);
"username" == t.name && ("7" == i ? o.html(r.fieldOptions.label.channel_id) : "8" == i ? o.html(r.fieldOptions.label.link) : o.html(r.fieldOptions.label.username)), 
void 0 !== t.disabled && t.disabled && (n.prop("disabled", !0), $("input, textarea", $("#order_" + t.related + ".fields")).attr("data-related", t.name).addClass("counter").trigger("keyup")), 
a.removeClass("hidden"), a.hasClass("has-depends") && n.trigger("change");
}), void 0 !== t && t && (a.val(""), $('.fields input[type="checkbox"]').prop("checked", !1)), 
r.updateCharge(), r.initDatetime();
try {
$('[data-toggle="tooltip"]').tooltip();
} catch (e) {
console.log("Error with tooltip. Catch", e);
}
}
},
updateAverageTime: function() {
var e = $("#orderform-service"), t = this.services[e.val()], a = $("#field-orderform-fields-average_time"), e = $("#order_average_time");
a.addClass("hidden"), e.hide(), void 0 !== t && "string" == typeof t.average_time && t.average_time.length && /\d/.test(t.average_time) && (a.val(t.average_time), 
a.removeClass("hidden"), e.show());
},
updateDescription: function() {
var e = $("#orderform-service"), t = this.services[e.val()], a = $("#service_description"), e = $("div", a);
e.html(""), a.addClass("hidden"), void 0 !== t && "string" == typeof t.description && t.description.length && (e.html(t.description), 
a.removeClass("hidden"));
},
updateQuantityHelpBlock: function() {
var e = $("#orderform-service"), t = this.services[e.val()], a = $("#order_quantity"), e = $("#order_min");
$(".min-max", a).remove(), $(".min-max", e).remove(), void 0 !== t && t.min_max_label && t.min_max_label.length && (t = '<small class="help-block min-max">' + t.min_max_label + "</small>", 
$("#field-orderform-fields-quantity", a).after(t), $("#order_count", e).after(t));
},
initFields: function() {
var a = this, e = a.fieldOptions.fields, n = "";
a.fieldsContainer.html("");
var o = [];
$.each(e, function(e, t) {
"function" == typeof (n = templates["neworder/order_" + t]) && o.push(n(a.fieldOptions));
}), a.fieldsContainer.html(o.join("\r\n")), a.initDatetime();
},
initDatetime: function() {
$(".datetime").length && $(".datetime").datetimepicker({
format: "DD/MM/YYYY",
minDate: new Date(),
useCurrent: !1,
icons: {
previous: "fa fa-chevron-left",
next: "fa fa-chevron-right"
},
widgetPositioning: {
horizontal: "auto",
vertical: $("body.body").length ? "top" : "auto"
}
});
}
}, customModule.siteSettings = {
run: function(e) {
$("#api_key").tooltip({
title: e.title
});
}
}, customModule.siteTickets = {
run: function(a) {
$("#ticketsend").submit(function(e) {
e.preventDefault();
var t = $("#send"), e = $(this);
return t.hasClass("active") || (t.addClass("active"), $.post(a.createTicketUrl, e.serialize(), function(e) {
t.removeClass("active"), "success" == e.status && ($(".ticket-danger").hide(), window.location.reload(!0)), 
"error" == e.status && ($(".ticket-danger div").html(e.error), $(".ticket-danger").show());
})), !1;
});
}
};