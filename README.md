CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Customization
 * Maintainers


INTRODUCTION
------------

The Site Feedback module allows users browsing your site to submit
feedback/suggestion and in cases bugs. Site Administrators can
manage this submitted feedback list under "Reports»Site feedback list".
All the feedbacks submitted will be saved as "In Progress"
and later Site Admins can have a look at this feedback,
perform necessary action and then mark the feedback as done.
Site Admins can also reply to the user who has submitted
the feedback from within manage feedback list.


REQUIREMENTS
------------

No special requirements


INSTALLATION
------------

Installation can be done in different ways. See:
https://www.drupal.org/docs/extending-drupal/installing-modules for further
information. Have a look into the downloading way. There is no
composer support yet.


CONFIGURATION
-------------

 * Configure user permissions in Administration » People » Permissions:

   - Manage Site Feedback

     Users in roles with the "Manage Site Feedback" permission will be able
     to see the feedback
     list under "Reports»Site feedback list" and can perform any operations
     on all submitted feedback.

   - Provide Site Feedback

     Users in roles with the "Provide Site Feedback" permission can submit the
     feedback/suggestion in the system.


CUSTOMIZATION
-------------

* To override the default style of the "Feedback" button, you may use
  the class "feedback-add-btn" and change the button accordingly.

* To override the default style of the "Mark as Done" and "Delete Selected"
  buttons, you may use the class "feedback-mark-done-btn" and
  "feedback-delete-btn" respectively.

* To override the style of view page, use the class "feedback-individual-view"
  and for each item "feedback-item"


MAINTAINERS
-----------

Current maintainers:
 * Deepesh Khanal (deepesh.khanal) - https://www.drupal.org/user/2909955
