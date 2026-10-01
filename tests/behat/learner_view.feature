@mod @mod_leitbox @javascript
Feature: Learners practise the cards of a LeitBox activity
  In order to learn with flashcards
  As a learner
  I need to open the learning view, answer cards and see my progress

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam       | Student  | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | name         | course | idnumber |
      | leitbox  | Flashcards 1 | C1     | leitbox1 |

  Scenario: The learning view loads its strings and cards through Moodle's web services
    When I am on the "Flashcards 1" "leitbox activity" page logged in as "student1"
    Then I should see "How does this work?"
    And I should see "5 Cards"
    And I should not see "[["

  Scenario: Answering a card moves it to the next box
    Given I am on the "Flashcards 1" "leitbox activity" page logged in as "student1"
    When I click on ".rc-box.rc-box--active" "css_element"
    Then I should see "Card 1 of 5"
    And I should see "Tap to flip"
    When I click on ".flip-container" "css_element"
    And I click on "Got it" "button"
    Then I should see "Card 2 of 5"
    When I click on "Back to dashboard" "button"
    Then I should see "4 Cards"
    And I should see "1 Card"
