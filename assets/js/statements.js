// Init single statement
function init_statement(id) {
    load_small_table_item(id, '#statement', 'statementid', 'statements/get_statement_data_ajax', '.table-statements');
}



// Statements quick total stats
function init_statement_total(manual) {
  if ($("#statements_total").length === 0) {
    return;
  }
  var _inv_total_inline = $(".statements-total-inline");
  var _inv_total_href_manual = $(".statements-total");

  if (
    $("body").hasClass("statements-total-manual") &&
    typeof manual == "undefined" &&
    !_inv_total_href_manual.hasClass("initialized")
  ) {
    return;
  }

  if (
    _inv_total_inline.length > 0 &&
    _inv_total_href_manual.hasClass("initialized")
  ) {
    // On the next request won't be inline in case of currency change
    // Used on dashboard
    _inv_total_inline.removeClass("statements-total-inline");
    return;
  }

  _inv_total_href_manual.addClass("initialized");
  var _years = $("body")
    .find('select[name="statements_total_years"]')
    .selectpicker("val");
  var years = [];
  $.each(_years, function (i, _y) {
    if (_y !== "") {
      years.push(_y);
    }
  });

  var currency = $("body").find('select[name="total_currency"]').val();
  var data = {
    currency: currency,
    years: years,
    init_total: true,
  };

  var project_id = $('input[name="project_id"]').val();
  var customer_id = $('.customer_profile input[name="userid"]').val();
  if (typeof project_id != "undefined") {
    data.project_id = project_id;
  } else if (typeof customer_id != "undefined") {
    data.customer_id = customer_id;
  }
  $.post(admin_url + "statements/get_statements_total", data).done(function (
    response
  ) {
    $("#statements_total").html(response);
  });
}

// Record payment function
function record_remittance(id) {
  if (typeof id == "undefined" || id === "") {
    return;
  }
  $("#statement").load(admin_url + "statements/record_statement_payment_ajax/" + id);
}

function schedule_remittance_send(id) {
  $("#statement").load(admin_url + "email_schedule_invoice/create/" + id);
}


function init_statements_body(){
  $("body").on("click", "#statement_create_credit_note", function (e) {
    if ($(this).attr("data-status") == 2) {
      return true;
    } else {
      var $m = $("#confirm_credit_note_create_from_statement");
      $m.modal("show");
      $m.find("#confirm-statement-credit-note").attr(
        "href",
        $(this).attr("href")
      );
      e.preventDefault();
    }
  });

  $("body").on(
    "change blur",
    ".apply-credits-to-statement .apply-credits-field",
    function () {
      var $applyCredits = $("#apply_credits");
      var $amountInputs = $applyCredits.find("input.apply-credits-field");
      var total = 0;
      var creditsRemaining = $applyCredits.attr("data-credits-remaining");

      $.each($amountInputs, function () {
        if ($(this).valid() === true) {
          var amount = $(this).val();
          amount = parseFloat(amount);
          if (!isNaN(amount)) {
            total += amount;
          } else {
            $(this).val(0);
          }
        }
      });

      $applyCredits.find("#credits-alert").remove();
      $applyCredits.find(".amount-to-credit").html(format_money(total));
      if (creditsRemaining < total) {
        $(".credits-table").before(
          $("<div/>", {
            id: "credits-alert",
            class: "alert alert-danger",
          }).html(
            app.lang.credit_amount_bigger_then_credit_note_remaining_credits
          )
        );
        $applyCredits.find('[type="submit"]').prop("disabled", true);
      } else {
        $applyCredits
          .find(".credit-note-balance-due")
          .html(format_money(creditsRemaining - total));
        $applyCredits.find('[type="submit"]').prop("disabled", false);
      }
    }
  );

  $("body").on(
    "change blur",
    ".apply-credits-from-statement .apply-credits-field",
    function () {
      var $applyCredits = $("#apply_credits");
      var $amountInputs = $applyCredits.find("input.apply-credits-field");
      var total = 0;
      var statementBalanceDue = $applyCredits.attr("data-balance-due");

      $.each($amountInputs, function () {
        if ($(this).valid() === true) {
          var amount = $(this).val();
          amount = parseFloat(amount);
          if (!isNaN(amount)) {
            total += amount;
          } else {
            $(this).val(0);
          }
        }
      });

      $applyCredits.find("#credits-alert").remove();
      $applyCredits.find(".amount-to-credit").html(format_money(total));
      if (total > statementBalanceDue) {
        $(".credits-table").before(
          $("<div/>", {
            id: "credits-alert",
            class: "alert alert-danger",
          }).html(app.lang.credit_amount_bigger_then_statement_balance)
        );
        $applyCredits.find('[type="submit"]').prop("disabled", true);
      } else {
        $applyCredits
          .find(".statement-balance-due")
          .html(format_money(statementBalanceDue - total));
        $applyCredits.find('[type="submit"]').prop("disabled", false);
      }
    }
  );


  table_statements = $("table.table-statements");

  if (table_statements.length > 0) {
    // Statements additional server params
    var Sales_table_ServerParams = {};
    var Sales_table_Filter = $("._hidden_inputs._filters input");

    $.each(Sales_table_Filter, function () {
      Sales_table_ServerParams[$(this).attr("name")] =
        '[name="' + $(this).attr("name") + '"]';
    });

    if (table_statements.length) {
      // Statements tables
      initDataTable(
        table_statements,
        admin_url +
          "statements/table" +
          ($("body").hasClass("recurring") ? "?recurring=1" : ""),
        "undefined",
        "undefined",
        Sales_table_ServerParams,
        !$("body").hasClass("recurring")
          ? [
              [3, "desc"],
              [0, "desc"],
            ]
          : [table_statements.find("th.next-recurring-date").index(), "asc"]
      );
    }

  }


}
