Feature: Manage bbPress Replies

  Scenario: Reply CRUD commands
    Given a bbPress install

    When I run `wp bbp forum create --title="Forum" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID}

    When I run `wp bbp topic create --title="Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp reply create --content="Content" --topic-id={TOPIC_ID} --forum-id={FORUM_ID} --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {REPLY_ID}

    When I run `wp bbp reply list --format=ids`
    Then STDOUT should contain:
      """
      {REPLY_ID}
      """

    When I run `wp bbp reply trash {REPLY_ID}`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully trashed.
      """

    When I run `wp bbp reply untrash {REPLY_ID}`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully untrashed.
      """

    When I run `wp bbp reply spam {REPLY_ID}`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully spammed.
      """

    When I run `wp bbp reply ham {REPLY_ID}`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully hammed.
      """

    When I run `wp bbp reply delete {REPLY_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully deleted.
      """

    When I try `wp bbp reply delete {REPLY_ID} --yes`
    Then the return code should be 1
