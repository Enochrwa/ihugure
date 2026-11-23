# Troubleshooting

## A player pays, presses the button to start the questions, and the page fails (HTTP 500)

**Cause.** Before a question is asked, the game looks for a question that is in the player's category, exists in the
player's language, and **has never been shown to that same player** (the app never repeats a question for a
player). If there is none, the old code crashed with `Column 'questionid' cannot be null` (preceded by
`Trying to access array offset on null` warnings in `services/quiz.php`).

It happens when:

1. the database has no questions at all (`schema.sql` creates empty tables; it has no data),
2. the player chose a language that has no questions, or
3. that player has already seen every question in the category (very easy with a small test set and one phone number).

**What the app does now.** It no longer crashes. The player sees "No new questions available" (in their
language) and can press Next to try again; as soon as questions exist the same paid game continues. If the player
had earned something and then ran out (after saying "No" to withdrawing), Next returns to the withdraw question.
Every occurrence is written to `game_logs` as `NO_QUESTION`.

**Find out why it happened**

```sql
SELECT addtime, gameid, info FROM game_logs WHERE event = 'NO_QUESTION' ORDER BY id DESC LIMIT 20;
```

In `info`, `"in_language_and_category":0` means the questions are missing for that category and language; a number
above 0 means the player has already seen them all. `INCOMPLETE_QUESTION` means a question exists but has no answers
in that language.

**Fix it.** Add questions: for a local database run `mysql -u ihugure -p ihugure < seed-sample.sql` (15 English
sample questions, safe to repeat). For real content, add each question with its answers for every language you
offer (see `docs/I18N.md`).

**Players who paid and got nothing.** Their game stays paid and unstarted, and is reused if they choose the same
language, category and amount again. To list them (for a refund or a message):

```sql
SELECT id, useremail, amount_invested, created_at FROM game WHERE payment_status = 'SUCCESSFUL' AND state IS NULL;
```
