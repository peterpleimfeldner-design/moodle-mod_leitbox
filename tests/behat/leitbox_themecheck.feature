@mod @mod_leitbox @leitbox_themecheck @javascript
Feature: The LeitBox learning view works with the theme Boost Union
  In order to practise flashcards with a common theme
  As a learner
  I need the dialogs, a round and the ratings to work with Boost Union as they do with Boost
  # Runs only in the CI job "Theme check", which installs Boost Union. The main Behat run leaves
  # this feature out (tag @leitbox_themecheck), because Boost Union is not installed there.

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
    And the following config values are set as admin:
      | theme | boost_union |

  Scenario: A learner opens and closes the dialogs and practises a round with Boost Union
    Given I am on the "Flashcards 1" "leitbox activity" page logged in as "student1"
    And the LeitBox page should use the "boost_union" theme
    And I should see "5 Cards"
    When I click on "How does this work?" "button"
    Then I should see "The Leitner box system"
    And I click on ".rc-overlay .rc-modal-close" "css_element"
    And I should not see "The Leitner box system"
    When I click on "Reset Learning Progress" "button"
    Then I should see "Reset Learning Progress?"
    And I click on ".rc-modal--warning .rc-btn--ghost" "css_element"
    And I should not see "Reset Learning Progress?"
    And I should see "5 Cards"
    When I click on ".rc-box.rc-box--active" "css_element"
    Then I should see "Card 1 of 5"
    And I should see "Tap to flip"
    # Each rating replaces the card with an animation; the next card is flipped only once the
    # rating buttons of the previous card are gone. Turning a card takes 0.7 seconds, and the
    # rating buttons can only be clicked once the back side faces the learner.
    And I click on ".flip-container" "css_element"
    And I wait "1" seconds
    And I click on "Hard" "button"
    And I should see "Card 2 of 5"
    And I wait "1" seconds
    And "Got it" "button" should not exist
    And I click on ".flip-container" "css_element"
    And I wait "1" seconds
    And I click on "Again" "button"
    And I should see "Card 3 of 5"
    And I wait "1" seconds
    And "Got it" "button" should not exist
    And I click on ".flip-container" "css_element"
    And I wait "1" seconds
    And I click on "Got it" "button"
    And I should see "Card 4 of 5"
    And I wait "1" seconds
    And "Got it" "button" should not exist
    And I click on ".flip-container" "css_element"
    And I wait "1" seconds
    And I click on "Got it" "button"
    And I should see "Card 5 of 5"
    And I wait "1" seconds
    And "Got it" "button" should not exist
    And I click on ".flip-container" "css_element"
    And I wait "1" seconds
    And I click on "Got it" "button"
    # Three of five known and one uncertain is the result "Solid Performance".
    And I should see "Solid Performance"
    When I click on "Back to dashboard" "button"
    Then I should see "4 Cards"
    And I should see "1 Card"
