<?php
/**
 * This file is part of PHPTAGS, a PHP source code tagging system.
 *
 * Copyright 2014 Joris Steyn <jorissteyn@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace PhpTags;

use SQLite3;

class Database
{
    /**
     * @var SQLite3 $db
     */
    protected $db;

    /**
     * @param SQLite3 $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->createSchema();
        $this->enablePcreUdf();
        $this->tweakPerformance();
    }

    /**
     * Create the schema in new database files.
     *
     * @param string $filePath
     * @return Database
     */
    protected function createSchema()
    {
        $this->db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS tags (
    name TEXT,
    file TEXT,
    tag_type TEXT,
    usage_type TEXT,
    start_line INTEGER,
    end_line INTEGER,
    start_file_pos INTEGER,
    end_file_pos INTEGER,
    comment TEXT
);
CREATE INDEX IF NOT EXISTS tags_name_index ON tags (name);
CREATE INDEX IF NOT EXISTS tags_file_index ON tags (file);
CREATE INDEX IF NOT EXISTS tags_tag_type_index ON tags (tag_type);
CREATE INDEX IF NOT EXISTS tags_usage_type_index ON tags (usage_type);
SQL
        );

        return $this;
    }

    /**
     * Set SQLite to write to disk as little as possible.
     *
     * @return Database
     */
    protected function tweakPerformance()
    {
        $this->db->exec(<<<SQL
PRAGMA synchronous=OFF;
PRAGMA count_changes=OFF;
PRAGMA journal_mode=MEMORY;
PRAGMA temp_store=MEMORY;
SQL
        );

        return $this;
    }

    /**
     * Create a user-defined REGEXP function.
     *
     * @return Database
     */
    protected function enablePcreUdf()
    {
        $result = $this->db->createFunction('regexp', function($pattern, $subject) {
            return preg_match($pattern, $subject);
        });

        if (!$result) {
            throw new \Exception(
                'Error registering user-defined REGEXP function in SQLite.'
            );
        }

        return $this;
    }

    /**
     * Add tags belonging to the file $filePath.
     *
     * @param string $filePath
     * @param array $tags
     * @return Database
     */
    public function addTags($filePath, $tags)
    {
        $this->db->exec('BEGIN TRANSACTION');

        foreach ($tags as $tag) {
            $statement = $this->db->prepare(<<<SQL
INSERT INTO tags (name, file, tag_type, usage_type, start_line, end_line, start_file_pos, end_file_pos, comment) VALUES
                 (:name, :file, :tag_type, :usage_type, :start_line, :end_line, :start_file_pos, :end_file_pos, :comment)
SQL
            );

            $tagData = [
                'path'           => $filePath,
                'name'           => $tag->getName(),
                'tag_type'       => $tag->getTagType(),
                'usage_type'     => $tag->getUsageType(),
                'start_line'     => $tag->getStartLine(),
                'start_file_pos' => $tag->getStartFilePos() + 1,
                'end_line'       => $tag->getEndLine(),
                'end_file_pos'   => $tag->getEndFilePos(),
                'comment'        => $tag->getComment(),
            ];

            $statement->bindParam(':file', $tagData['path'], SQLITE3_TEXT);
            $statement->bindParam(':name', $tagData['name'], SQLITE3_TEXT);
            $statement->bindParam(':comment', $tagData['comment'], SQLITE3_TEXT);
            $statement->bindParam(':tag_type', $tagData['tag_type'], SQLITE3_TEXT);
            $statement->bindParam(':usage_type', $tagData['usage_type'], SQLITE3_TEXT);

            $statement->bindParam(':end_line', $tagData['end_line'], SQLITE3_INTEGER);
            $statement->bindParam(':end_file_pos', $tagData['end_file_pos'], SQLITE3_INTEGER);

            $statement->bindParam(':start_line', $tagData['start_line'], SQLITE3_INTEGER);
            $statement->bindParam(':start_file_pos', $tagData['start_file_pos'], SQLITE3_INTEGER);

            $statement->execute();
        }

        $this->db->exec('COMMIT');

        return $this;
    }

    /**
     * Purge all tags in the database.
     *
     * @return Database
     */
    public function truncate()
    {
        $this->db->exec('DELETE FROM tags');

        return $this;
    }

    /**
     * Purge the current tags belonging to $filePath.
     *
     * @param string $filePath
     * @return Database
     */
    public function purgeTags($filePath)
    {
        $statement = $this->db->prepare('DELETE FROM tags WHERE file = :file');
        $statement->bindParam(':file', $filePath, SQLITE3_TEXT);
        $statement->execute();

        return $this;
    }

    /**
     * Find all tags matching a pattern.
     *
     * @param string $pattern
     * @param string $usageType
     * @param bool $ignoreCase
     * @param string $tagType
     * @return array
     */
    public function findByPattern($pattern, $usageType, $ignoreCase = true, $tagType = null)
    {
        $sql = <<<SQL
SELECT * FROM tags WHERE name REGEXP :pattern
    AND usage_type = :usage_type
SQL;

        if ($tagType) {
            $sql .= ' AND tag_type = :tag_type';
        }

        // Add and escape delimiters.
        $pattern = str_replace('#', '\\#', $pattern);   // Escape delimiters.
        $pattern = sprintf('#%s#', $pattern);

        if ($ignoreCase) {
            $pattern .= 'i';
        }

        $statement = $this->db->prepare($sql);
        $statement->bindParam(':pattern', $pattern, SQLITE3_TEXT);
        $statement->bindParam(':usage_type', $usageType, SQLITE3_TEXT);

        if ($tagType) {
            $statement->bindParam(':tag_type', $tagType, SQLITE3_TEXT);
        }

        return $this->resultToArray(
            $statement->execute()
        );
    }

    /**
     * Convert a result object to array of associative arrays.
     *
     * @param \SQLite3Result
     * @return array
     */
    protected function resultToArray(\SQLite3Result $result)
    {
        $tags = array();

        while ($tag = $result->fetchArray(SQLITE3_ASSOC)) {
            $tags[] = $tag;
        }

        return $tags;
    }
}
