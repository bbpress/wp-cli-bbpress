Feature: Manage bbPress Topics

	Scenario: Topic CRUD commands
    Given a bbPress install

    When I run `wp bbp topic create --title="CRUD Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp topic list --format=ids`
    Then STDOUT should contain:
      """
      {TOPIC_ID}
      """

		When I run `wp bbp topic trash {TOPIC_ID}`
		Then STDOUT should contain:
			"""
			Success: Topic {TOPIC_ID} successfully trashed.
			"""

		When I run `wp bbp topic untrash {TOPIC_ID}`
		Then STDOUT should contain:
			"""
			Success: Topic {TOPIC_ID} successfully untrashed.
			"""

    When I run `wp bbp topic spam {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully spammed.
      """

    When I run `wp bbp topic ham {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully hammed.
      """

		When I run `wp bbp topic delete {TOPIC_ID} --yes`
		Then STDOUT should contain:
			"""
			Success: Topic {TOPIC_ID} successfully deleted.
			"""

		When I try `wp bbp topic delete {TOPIC_ID} --yes`
    Then the return code should be 1

  Scenario: Testing close/open commands
    Given a bbPress install

    When I run `wp bbp topic create --title="Close Topic" --status=closed --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp topic open {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully opened.
      """

    When I run `wp bbp topic close {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully closed.
      """

    When I run `wp bbp topic open {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully opened.
      """

    When I run `wp bbp topic delete {TOPIC_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully deleted.
      """

    When I try `wp bbp topic delete {TOPIC_ID} --yes`
    Then the return code should be 1

  Scenario: Testing approve/unapprove commands
    Given a bbPress install

    When I run `wp bbp topic create --title="Approve Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp topic unapprove {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully unapproved.
      """

    When I run `wp bbp topic approve {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully approved.
      """

    When I run `wp bbp topic delete {TOPIC_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully deleted.
      """

    When I try `wp bbp topic delete {TOPIC_ID} --yes`
    Then the return code should be 1

  Scenario: Testing stick/unstick commands
    Given a bbPress install

    When I run `wp bbp topic create --title="Stick Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp topic stick {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully sticked.
      """

    When I run `wp bbp topic unstick {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully unsticked.
      """

    When I run `wp bbp topic delete {TOPIC_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Topic {TOPIC_ID} successfully deleted.
      """

    When I try `wp bbp topic delete {TOPIC_ID} --yes`
    Then the return code should be 1
