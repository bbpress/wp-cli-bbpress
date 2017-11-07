Feature: Manage bbPress Moderators

  Scenario: Add a forum moderator
    Given a bbPress install

    When I run `wp bbp user moderator add --forum-id=545646 --user-id=465456`
    Then STDOUT should contain:
      """
      Success: Member added as a forum moderator.
      """

  Scenario: Remove a forum moderator
    Given a bbPress install

    When I run `wp bbp user moderator remove --forum-id=456456 --user-id=4995`
    Then STDOUT should contain:
      """
      Success: Member removed as a moderator.
      """

  Scenario: List forum moderators
    Given a bbPress install

    When I run `wp bbp user moderator list --forum-id=456456 --format=count`
    Then STDOUT should contain:
      """
      6
      """

    When I run `wp bbp user moderator list --forum-id=45456 --format=ids`
    Then STDOUT should contain:
      """
      5421 454 654654 5454 545
      """
