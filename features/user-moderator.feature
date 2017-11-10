Feature: Manage bbPress Moderators

  Scenario: User moderators operations
    Given a bbPress install

    When I run `wp bbp forum create --title="Forum Title" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID}

    When I run `wp user create testuser2 testuser2@example.com --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {USER_ID}

    When I run `wp bbp user moderator add --forum-id={FORUM_ID} --user-id={USER_ID}`
    Then STDOUT should contain:
      """
      Success: Member added as a moderator.
      """

    When I run `wp bbp user moderator remove --forum-id={FORUM_ID} --user-id={USER_ID}`
    Then STDOUT should contain:
      """
      Success: Member removed as a moderator.
      """

    When I run `wp bbp user moderator list --forum-id={FORUM_ID} --format=count`
    Then STDOUT should contain:
      """
      1
      """

    When I run `wp bbp user moderator list --forum-id={FORUM_ID} --format=ids`
    Then STDOUT should contain:
      """
      {USER_ID}
      """
