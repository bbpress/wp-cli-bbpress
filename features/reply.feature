Feature: Manage bbPress Replies

  Scenario: Reply CRUD commands
    Given a bbPress install

    When I run `wp bbp reply create --content="Reply" --status=publish --porcelain`
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

  Scenario: Testing approve/unapprove commands
    Given a bbPress install

    When I run `wp user create testuser1 testuser1@example.com --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {MEMBER_ID}

    When I run `wp bbp reply create --title="Approve Reply" --user-id={MEMBER_ID} --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {REPLY_ID}

    When I run `wp bbp reply unapprove {REPLY_ID}`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully unapproved.
      """

    When I run `wp bbp reply approve {REPLY_ID}`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully approved.
      """

    When I run `wp bbp reply delete {REPLY_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Reply {REPLY_ID} successfully deleted.
      """

    When I try `wp bbp reply delete {REPLY_ID} --yes`
    Then the return code should be 1

  Scenario: Reply List
    Given a bbPress install

    When I run `wp bbp reply create --title="Reply 01" --content="Reply" --status=pending --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {REPLY_ID}

    When I run `wp bbp reply create --title="Reply 02" --content="Another Reply" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {REPLY_ID_2}

    When I run `wp bbp reply list --format=count`
    Then STDOUT should be:
      """
      2
      """

    When I run `wp bbp reply list --post_status=pending --format=count`
    Then STDOUT should be:
      """
      1
      """

    When I run `wp bbp reply list --format=ids`
    Then STDOUT should be:
      """
      {REPLY_ID} {REPLY_ID_2}
      """

    When I run `wp bbp reply list --fields=id,post_title,post_status --format=csv`
    Then STDOUT should be CSV containing:
      | id           | post_title  |  post_status  |
      | {REPLY_ID}   | Reply 01    |  pending      |
      | {REPLY_ID_2} | Reply 02    |  publish      |
