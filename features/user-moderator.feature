Feature: Manage bbPress Moderators

   Scenario: User moderators operations
    Given a bbPress install

    When I try `wp user get bogus-user`
    Then the return code should be 1
    And STDOUT should be empty

    When I run `wp user create testuser2 testuser2@example.com --first_name=test --last_name=user --role=author --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {USER_ID}

    When I run `wp bbp user moderator add --forum-id=545646 --user-id={USER_ID}`
    Then STDOUT should contain:
      """
      Success: Member added as a forum moderator.
      """

    When I run `wp bbp user moderator remove --forum-id=456456 --user-id={USER_ID}`
    Then STDOUT should contain:
      """
      Success: Member removed as a moderator.
      """

    When I run `wp bbp user moderator list --forum-id=456456 --format=count`
    Then STDOUT should contain:
      """
      {USER_ID}
      """

    When I run `wp bbp user moderator list --forum-id=45456 --format=ids`
    Then STDOUT should contain:
      """
      {USER_ID}
      """
