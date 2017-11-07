Feature: Manage bbPress Users

  Scenario: Mark a user's topics and replies as spam
    Given a bbPress install

    When I run `wp bbp user spam --user-id=465456`
    Then STDOUT should contain:
      """
      Success: User topics and replies marked as spam.
      """

  Scenario: Mark a user's topics and replies as ham
    Given a bbPress install

    When I run `wp bbp user ham --user-id=465456`
    Then STDOUT should contain:
      """
      Success: User topics and replies marked as ham.
      """

  Scenario: Set user role
    Given a bbPress install

    When I run `wp bbp user set_role --user-id=465456 --role=moderator`
    Then STDOUT should contain:
      """
      Success: New role for user set: moderator
      """

  Scenario: Get URL of the user profile page
    Given a bbPress install

    When I run `wp bbp user permalink {USER}`
    Then STDOUT should contain:
      """
      Success: User profile page: https://example.com/user-slug
      """
