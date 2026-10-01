@mod @mod_leitbox
Feature: Teachers manage the cards of a LeitBox activity
  In order to prepare flashcards for my learners
  As an editing teacher
  I need to add, edit and delete cards

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Tom       | Teacher  | teacher1@example.com |
      | teacher2 | Nina      | Helper   | teacher2@example.com |
      | student1 | Sam       | Student  | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | teacher        |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | name         | course | idnumber |
      | leitbox  | Flashcards 1 | C1     | leitbox1 |

  Scenario: An editing teacher adds, edits and deletes a card
    Given I am on the "Flashcards 1" "mod_leitbox > Manage cards" page logged in as "teacher1"
    When I set the following fields to these values:
      | Question | What is the capital of Austria? |
      | Answer   | Vienna                          |
    And I press "Save Card"
    Then I should see "Card added successfully."
    And I should see "What is the capital of Austria?"
    And I should see "Vienna"
    When I click on ".leitbox-edit-card" "css_element" in the "What is the capital of Austria?" "table_row"
    And I set the field "Answer" to "Wien"
    And I press "Save changes"
    Then I should see "Card updated successfully."
    And I should see "Wien"
    And I should not see "Vienna"
    When I click on ".leitbox-delete-card" "css_element" in the "What is the capital of Austria?" "table_row"
    Then I should see "Card deleted."
    And I should see "No cards found in this activity."

  Scenario: The card management page is offered only to roles that may manage cards
    When I am on the "Flashcards 1" "leitbox activity" page logged in as "teacher1"
    Then "Manage Cards" "link" should exist in current page administration
    When I am on the "Flashcards 1" "leitbox activity" page logged in as "teacher2"
    Then "Manage Cards" "link" should not exist in current page administration
    When I am on the "Flashcards 1" "leitbox activity" page logged in as "student1"
    Then "Manage Cards" "link" should not exist in current page administration

  @javascript
  Scenario: The card management page loads without JavaScript errors
    When I am on the "Flashcards 1" "mod_leitbox > Manage cards" page logged in as "teacher1"
    Then I should see "Existing cards"
    And I should see "Bulk Import"
