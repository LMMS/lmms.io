# Use script to convert database contents to a format compatible with lsp code after 2020-03-22
import mysql.connector
import html
import logging

HOST = "localhost"
USER = "lsp"
PASSWORD = ""
DATABASE = "lsp"

LIST_FILES = 'SELECT id, filename, description FROM files'
UPDATE_FILES = 'UPDATE files SET filename = %s, description = %s WHERE id = %s'
LIST_USERS = 'SELECT id, login, realname FROM users'
UPDATE_USERS = 'UPDATE users SET login = %s, realname = %s WHERE id = %s'
LIST_COMMENTS = 'SELECT id, text FROM comments'
UPDATE_COMMENTS = 'UPDATE comments SET text = %s WHERE id = %s'


def update_file_record(record: tuple):
    new_record = tuple([record[0],
                        html.unescape(record[1]),
                        html.unescape(record[2])]
                       )
    return new_record


def update_comment_record(record: tuple):
    new_record = tuple([record[0], html.unescape(record[1])])
    return new_record


def convert_files_records(cursor: mysql.connector.cursor.MySQLCursor):
    needs_update = []
    cursor.execute(LIST_FILES)
    records = cursor.fetchall()
    logging.info("Scanning file records...")
    for record in records:
        updated_record = update_file_record(record)
        if record != updated_record:
            updated_record = tuple([updated_record[1], updated_record[2], updated_record[0]])
            needs_update.append(updated_record)
    logging.info("Updating %d records...", len(needs_update))
    cursor.executemany(UPDATE_FILES, needs_update)


def convert_users_records(cursor: mysql.connector.cursor.MySQLCursor):
    needs_update = []
    cursor.execute(LIST_USERS)
    records = cursor.fetchall()
    logging.info("Scanning user records...")
    for record in records:
        updated_record = update_file_record(record)
        if record != updated_record:
            updated_record = tuple([updated_record[1], updated_record[2], updated_record[0]])
            needs_update.append(updated_record)
    logging.info("Updating %d records...", len(needs_update))
    cursor.executemany(UPDATE_USERS, needs_update)


def convert_comments_records(cursor: mysql.connector.cursor.MySQLCursor):
    needs_update = []
    cursor.execute(LIST_COMMENTS)
    records = cursor.fetchall()
    logging.info("Scanning comment records...")
    for record in records:
        updated_record = update_comment_record(record)
        if record != updated_record:
            updated_record = tuple([updated_record[1], updated_record[0]])
            needs_update.append(updated_record)
    logging.info("Updating %d records...", len(needs_update))
    cursor.executemany(UPDATE_COMMENTS, needs_update)


logging.basicConfig(level="INFO")
logging.info("Connecting to database...")
conn = mysql.connector.connect(
    host=HOST, user=USER, password=PASSWORD, database=DATABASE)
cursor = conn.cursor()
logging.info("Converting file records...")
convert_files_records(cursor)
logging.info("Converting user records...")
convert_users_records(cursor)
logging.info("Converting comment records...")
convert_comments_records(cursor)
conn.commit()
conn.close()
