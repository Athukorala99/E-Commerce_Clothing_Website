<?php

namespace Blocksy\Database;

class SearchReplace {
	private $dry_run = false;

	public function invoke($args = []) {
		global $wpdb;

		$args = wp_parse_args($args, [
			'old' => '',
			'new' => '',
			'dry_run' => true,
			'tables' => null
		]);

		$old = $args['old'];
		$new = $args['new'];
		$this->dry_run = $args['dry_run'];

		$skip_columns = [];

		// never mess with hashed passwords
		$skip_columns[] = 'user_pass';

		$tables = $args['tables'];

		if (! $tables) {
			$tables = Utils::wp_get_relevant_table_names();
		}

		$result = [
			'tables' => [],
		];

		foreach ($tables as $table) {
			$table_sql = $this->esc_sql_ident($table);

			list($primary_keys, $columns, $all_columns) = self::get_columns($table);

			// since we'll be updating one row at a time,
			// we need a primary key to identify the row
			if (empty($primary_keys)) {
				continue;
			}

			foreach ($columns as $col) {
				if (in_array($col, $skip_columns, true)) {
					continue;
				}

				$col_sql = $this->esc_sql_ident($col);
				$wpdb->last_error = '';

				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::esc_sql_ident
				$serial_row = $wpdb->get_row(
					"SELECT * FROM $table_sql WHERE $col_sql REGEXP '^[aiO]:[1-9]' LIMIT 1"
				);

				// When the regex triggers an error, we should fall back to PHP
				if (false !== strpos($wpdb->last_error, 'ERROR 1139')) {
					$serial_row = true;
				}

				if (null !== $serial_row) {
					$type = 'PHP';

					$count = $this->php_handle_col(
						$col,
						$primary_keys,
						$table,
						$old,
						$new
					);
				} else {
					$type = 'SQL';

					$count = $this->sql_handle_col(
						$col,
						$primary_keys,
						$table,
						$old,
						$new
					);
				}

				if (intval($count) > 0) {
					if (! isset($result['tables'][$table])) {
						$result['tables'][$table] = 0;
					}

					$result['tables'][$table] += intval($count);
				}
			}

		}

		$total = 0;

		foreach ($result['tables'] as $table => $count) {
			$total += $count;
		}

		$result['total'] = $total;

		return $result;
	}

	/**
	 * Escapes (backticks) MySQL identifiers (aka schema object names) - i.e. column names, table names, and database/index/alias/view etc names.
	 * See https://dev.mysql.com/doc/refman/5.5/en/identifiers.html
	 *
	 * @param string|array $idents A single identifier or an array of identifiers.
	 * @return string|array An escaped string if given a string, or an array of escaped strings if given an array of strings.
	 */
	private function esc_sql_ident($idents) {
		$backtick = static function ($v) {
			// Escape any backticks in the identifier by doubling.
			return '`' . str_replace('`', '``', $v) . '`';
		};

		if (is_string($idents)) {
			return $backtick($idents);
		}

		return array_map($backtick, $idents);
	}

	private function get_columns($table) {
		global $wpdb;

		$table_sql = $this->esc_sql_ident($table);
		$primary_keys = array();
		$text_columns = array();
		$all_columns = array();
		$suppress_errors = $wpdb->suppress_errors();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::esc_sql_ident
		$results = $wpdb->get_results("DESCRIBE $table_sql");

		if (! empty($results)) {
			foreach ($results as $col) {
				if ('PRI' === $col->Key) {
					$primary_keys[] = $col->Field;
				}

				if ($this->is_text_col($col->Type)) {
					$text_columns[] = $col->Field;
				}

				$all_columns[] = $col->Field;
			}
		}

		$wpdb->suppress_errors($suppress_errors);

		return array($primary_keys, $text_columns, $all_columns);
	}

	private function is_text_col($type) {
		foreach (array('text', 'varchar') as $token) {
			if (false !== stripos($type, $token)) {
				return true;
			}
		}

		return false;
	}

	private function sql_handle_col($col, $primary_keys, $table, $old, $new) {
		global $wpdb;

		$table_sql = $this->esc_sql_ident($table);
		$col_sql = $this->esc_sql_ident($col);

		if ($this->dry_run) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::esc_sql_ident
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT($col_sql) FROM $table_sql WHERE $col_sql LIKE BINARY %s;", '%' . $this->esc_like($old) . '%'
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::esc_sql_ident
			$count = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $table_sql SET $col_sql = REPLACE($col_sql, %s, %s);",
					$old,
					$new
				)
			);
		}

		return $count;
	}

	private function esc_like($old) {
		global $wpdb;

		// Remove notices in 4.0 and support backwards compatibility
		if (method_exists($wpdb, 'esc_like')) {
			// 4.0
			$old = $wpdb->esc_like($old);
		} else {
			// phpcs:ignore WordPress.WP.DeprecatedFunctions.like_escapeFound -- BC-layer for WP 3.9 or less.
			$old = like_escape(esc_sql($old)); // Note: this double escaping is actually necessary, even though `esc_like()` will be used in a `prepare()`.
		}

		return $old;
	}

	private function php_handle_col($col, $primary_keys, $table, $old, $new) {
		global $wpdb;

		$count = 0;
		$replacer = new SearchReplacer($old, $new, true);

		$table_sql = $this->esc_sql_ident($table);
		$col_sql = $this->esc_sql_ident($col);

		$base_key_condition = "$col_sql" . $wpdb->prepare(
			' LIKE BINARY %s',
			'%' . $this->esc_like($old) . '%'
		);

		$where_key = "WHERE $base_key_condition";

		$escaped_primary_keys = $this->esc_sql_ident($primary_keys);
		$primary_keys_sql = implode(',', $escaped_primary_keys);
		$order_by_keys = array_map(
			static function ($key) {
				return "{$key} ASC";
			},
			$escaped_primary_keys
		);

		$order_by_sql = 'ORDER BY ' . implode(',', $order_by_keys);
		$limit = 1000;

		// 2 errors:
		// - WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::esc_sql_ident
		// - WordPress.CodeAnalysis.AssignmentInCondition -- no reason to do copy-paste for a single valid assignment in while
		// phpcs:ignore
		while ($rows = $wpdb->get_results("SELECT {$primary_keys_sql} FROM {$table_sql} {$where_key} {$order_by_sql} LIMIT {$limit}")) {
			foreach ($rows as $keys) {
				$where_sql = '';

				foreach ((array) $keys as $k => $v) {
					if ('' !== $where_sql) {
						$where_sql .= ' AND ';
					}

					$where_sql .= $this->esc_sql_ident($k) . ' = ' . $this->esc_sql_value($v);
				}

				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- escaped through self::esc_sql_ident
				$col_value = $wpdb->get_var(
					"SELECT {$col_sql} FROM {$table_sql} WHERE {$where_sql}"
				);

				if ('' === $col_value) {
					continue;
				}

				$value = $replacer->run($col_value);

				if ($value === $col_value) {
					continue;
				}

				$count++;

				if (! $this->dry_run) {
					$update_where = array();

					foreach ((array) $keys as $k => $v) {
						$update_where[$k] = $v;
					}

					$wpdb->update($table, [$col => $value], $update_where);
				}
			}

			// Because we are ordering by primary keys from least to greatest,
			// we can exclude previous chunks from consideration by adding greater-than conditions
			// to insist the next chunk's keys must be greater than the last of this chunk's keys.
			$last_row = end($rows);
			$next_key_conditions = array();


			// NOTE: For a composite key (X, Y, Z), selecting the next rows requires the following conditions:
			// ( X = lastX AND Y = lastY AND Z > lastZ ) OR
			// ( X = lastX AND Y > lastY ) OR
			// ( X > lastX )
			for ($last_key_index = count($primary_keys) - 1; $last_key_index >= 0; $last_key_index--) {
				$next_key_subconditions = array();

				for ($i = 0; $i <= $last_key_index; $i++) {
					$k = $primary_keys[$i];
					$v = $last_row->{$k};

					if ($i < $last_key_index) {
						$next_key_subconditions[] = $this->esc_sql_ident($k) . ' = ' . $this->esc_sql_value($v);
					} else {
						$next_key_subconditions[] = $this->esc_sql_ident($k) . ' > ' . $this->esc_sql_value($v);
					}
				}

				$next_key_conditions[] = '( ' . implode(' AND ', $next_key_subconditions) . ' )';
			}


			$where_key_conditions = array();

			if ($base_key_condition) {
				$where_key_conditions[] = $base_key_condition;
			}

			$where_key_conditions[] = '( ' . implode(' OR ', $next_key_conditions) . ' )';

			$where_key = 'WHERE ' . implode(' AND ', $where_key_conditions);
		}

		return $count;
	}

	/**
	 * Puts MySQL string values in single quotes, to avoid them being interpreted as column names.
	 *
	 * @param string|array $values A single value or an array of values.
	 * @return string|array A quoted string if given a string, or an array of quoted strings if given an array of strings.
	 */
	private function esc_sql_value($values) {
		$quote = static function ($v) {
			// Don't quote integer values to avoid MySQL's implicit type conversion.
			if (preg_match('/^[+-]?[0-9]{1,20}$/', $v)) { // MySQL BIGINT UNSIGNED max 18446744073709551615 (20 digits).
				return esc_sql($v);
			}

			// Put any string values between single quotes.
			return "'" . esc_sql($v) . "'";
		};

		if (is_array($values)) {
			return array_map($quote, $values);
		}

		return $quote($values);
	}
}

