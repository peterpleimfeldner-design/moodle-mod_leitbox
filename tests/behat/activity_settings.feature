@mod @mod_leitbox
Feature: Teachers edit the settings of a LeitBox activity
  In order to configure a LeitBox
  As an editing teacher
  I need to open and save the activity settings form

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Tom       | Teacher  | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | enablecompletion |
      | Course 1 | C1        | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name         | course | idnumber |
      | leitbox  | Flashcards 1 | C1     | leitbox1 |

  Scenario: The settings form opens with completion enabled and saves changes
    # Opening the form runs data_preprocessing() and add_completion_rules(),
    # which must work on every supported Moodle version (4.1 to 5.1).
    Given I am on the "Flashcards 1" "leitbox activity editing" page logged in as "teacher1"
    When I set the following fields to these values:
      | Activity Name | Flashcards renamed |
    And I press "Save and display"
    Then I should see "Flashcards renamed"
