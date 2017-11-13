Feature: Manage bbPress Forums

  Scenario: Forum CRUD commands
    Given a bbPress install

    When I run `wp bbp forum create --content="Forum" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID}

    When I run `wp bbp forum list --format=ids`
    Then STDOUT should contain:
      """
      {FORUM_ID}
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

    When I run `wp bbp forum delete {FORUM_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} and its topics and replies deleted.
      """

    When I try `wp bbp forum delete {FORUM_ID} --yes`
    Then the return code should be 1

  Scenario: Testing open/close commands
    Given a bbPress install

    When I run `wp bbp forum create --title="Open Forum" --status=close --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID}

    When I run `wp bbp forum close {FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} successfully closed.
      """

    When I run `wp bbp forum open {FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} successfully opened.
      """

    When I run `wp bbp forum delete {FORUM_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Forum {FORUM_ID} and its topics and replies deleted.
      """

    When I try `wp bbp forum delete {FORUM_ID} --yes`
    Then the return code should be 1

  Scenario: Forum List
    Given a bbPress install

    When I run `wp bbp forum create --title="Forum 01" --content="Forum" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID}

    When I run `wp bbp forum create --title="Forum 02" --content="Another Forum" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID_2}

    When I run `wp bbp forum list --format=count`
    Then STDOUT should be:
      """
      2
      """

    When I run `wp bbp forum list --fields=post_title,post_status --format=csv`
    Then STDOUT should be CSV containing:
      | post_title  |  post_status  |
      | Forum 01    |  pending      |
      | Forum 02    |  publish      |
