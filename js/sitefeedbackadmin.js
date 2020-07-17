(function ($) {
  Drupal.behaviors.siteFeedback = {
    attach: function (context, settings) {

      //Hide Mark as Done and Delete by default
      $('.feedback-mark-done-btn, .feedback-delete-btn, .feedback-action-confirm').hide();
      $('.feedback-mark-done-btn, .feedback-delete-btn').css('cursor', 'pointer');

      //Show the action buttons when clicking on checkbox
      $('input[type="checkbox"]').on('change', function (e) {
        if ($('#sitefeedback-admin-table').find("input[type='checkbox']:checked").length > 0) {
          $('.feedback-mark-done-btn, .feedback-delete-btn').show();
        }
        else {
          $('.feedback-mark-done-btn, .feedback-delete-btn').hide();
        }
      });

      //Mark as Done and Delete button event
      $('.feedback-mark-done-btn, .feedback-delete-btn').once().click(function () {
        let ids = [],
          operation = $(this).data('operation');
        $('input[type="checkbox"]:checked').each(function (index) {
          if ($(this).attr('id')) {
            ids.push($(this).val());
          }
        });

        //If no ids, show an alert
        if (!ids.length) {
          alert(Drupal.t('No feedback selected. Please select at least one feedback.'));
          return;
        }

        //From here, we can perform the actual operation
        $('.feedback-action-confirm').dialog({
          title: (operation == 'mark_done') ? Drupal.t('Mark as Done') : Drupal.t('Delete Selected'),
          buttons: [
            {
              text: Drupal.t("Yes"),
              "class": 'button button--primary',
              click: function () {
                $.ajax({
                  url: '/admin/sitefeedback/bulkaction',
                  method: "POST",
                  dataType: "json",
                  data: {
                    'operation': operation,
                    'ids': ids
                  },
                  success: function (jsonData) {
                    let msg;
                    if (jsonData.status == 200) {
                      //Show Success Msg
                      msg = Drupal.theme.message({'text': jsonData.message}, {
                        'type': 'status',
                        'id': 'action-success'
                      });
                    }
                    else {
                      //Show Error Msg
                      msg = Drupal.theme.message({'text': jsonData.message}, {
                        'type': 'error',
                        'id': 'action-error'
                      });
                    }
                    $('.messages--error[data-drupal-message-id="action-error"]').remove();
                    $('.messages--status[data-drupal-message-id="action-success"]').remove();
                    $('.region-highlighted').append(msg);
                    $('.feedback-action-confirm').dialog("close");
                    window.location.reload();
                  },
                  error: function (error) {
                    let msg;
                    //Show Error Msg
                    msg = Drupal.theme.message({'text': error.responseJSON.message}, {
                      'type': 'error',
                      'id': 'action-error'
                    });
                    $('.messages--error[data-drupal-message-id="action-error"]').remove();
                    $('.region-highlighted').append(msg);
                    $('.feedback-action-confirm').dialog("close");
                    window.location.reload();
                  }
                });
              }
            },
            {
              text: Drupal.t("Cancel"),
              click: function () {
                $(this).dialog("close");
              }
            }
          ],
          modal: true,
          draggable: false,
          resizable: false,
          overlay: {
            opacity: 0.5,
            background: "black"
          }
        });

      });
    },
  }
})(jQuery);
