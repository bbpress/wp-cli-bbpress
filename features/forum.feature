Feature: Manage bbPress Forums

  Scenario: Forum CRUD commands
    Given a bbPress install

    When I run `wp bbp forum create --title="Forum" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID}

    When I run `wp bbp forum list --format=ids`
    Then STDOUT should contain:
      """
      {FORUM_ID}
      """

    When I run `wp bbp forum open {FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} successfully opened.
      """

    When I run `wp bbp forum close {FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} successfully closed.
      """

    When I run `wp bbp forum trash {FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} and its topics trashed.
      """

    When I run `wp bbp forum untrash {FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} and its topics untrashed.
      """

    When I run `wp bbp forum delete {FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} and its topics and replies deleted.
      """
