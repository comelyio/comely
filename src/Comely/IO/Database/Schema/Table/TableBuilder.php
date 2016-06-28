<?php
declare(strict_types=1);

namespace Comely\IO\Database\Schema\Table;

use Comely\IO\Database\Schema\AbstractTable;
use Comely\Kernel\Repository;
use Comely\IO\Database\Exception\SchemaException;

/**
 * Class Builder
 * @package Comely\IO\Database\Schema\Table
 */
class TableBuilder extends Repository implements Constants
{
    private $dbDriver;
    private $columns;
    private $constraints;
    private $tableName;
    private $tableEngine;

    /**
     * @param AbstractTable $table
     * @return TableBuilder
     */
    public function setTable(AbstractTable $table) : self
    {
        $this->dbDriver =   $table->getDb()->driver();
        $this->columns  =   $table->getColumns();
        $this->constraints  =   $table->getConstraints();
        $this->tableName    =   $table->getName();
        $this->tableEngine  =   $table->getEngine();

        // Return self
        return $this;
    }
    
    /**
     * Generate CREATE TABLE query
     * 
     * @param bool $dropExisting
     * @return string
     * @throws SchemaException
     */
    public function buildQuery($dropExisting = false) : string
    {
        // Configure line endings and indents
        $lb =   "\n";
        $indent =   str_repeat(" ", 2);

        // Include command to drop existing table?
        if($dropExisting    === true) {
            // Works with MySQL and SQLite
            $sqlTable   =   sprintf("DROP TABLE IF EXISTS `%s`;%s", $this->tableName, $lb);
        } else {
            // Start with an empty String
            $sqlTable   =   "";
        }

        // CREATE TABLE statement
        $sqlTable   .=  sprintf("CREATE TABLE `%s` (%s", $this->tableName, $lb);

        // Empty arrays to collect relevant data for reprocessing
        $mySqlUniqueKeys    =   [];

        // Iterate through columns
        foreach($this->columns as $name => $column) {
            // Start column with indent
            $sqlTable   .=  sprintf("%s`%s` ", $indent, $name);

            // Check column type
            if($column->type  === "int") {
                // Integer table
                $integerTable   =   [
                    self::INT_TINY =>  "tinyint",
                    self::INT_SMALL =>  "smallint",
                    self::INT_MEDIUM =>  "mediumint",
                    self::INT_DEFAULT => "int",
                    self::INT_BIG   => "bigint"
                ];

                // Integer column type
                if($this->dbDriver  === "mysql") {
                    $sqlTable   .=  $integerTable[$column->flag];

                    // Number of digits explicitly specified?
                    if(array_key_exists("digits", $column->attributes)) {
                        $sqlTable   .=  sprintf("(%d)", $column->attributes["digits"]);
                    }
                } elseif($this->dbDriver    === "sqlite") {
                    // SQLite is a simple as it gets
                    $sqlTable   .=  "INTEGER";
                } elseif($this->dbDriver    === "pgsql") {
                    // PostgreSQL doesn't have AI
                    if(array_key_exists("ai", $column->attributes)) {
                        // SERIAL Type
                    } else {
                        // Integer Type
                    }
                }
            } elseif($column->type    === "string") {
                // String column type (char|varchar)
                if($this->dbDriver  === "mysql") {
                    // MySQL
                    // Determine if its a CHAR or VARCHAR
                    $sqlTable   .=  ($column->flag  === self::STR_FIXED) ? "char" : "varchar";

                    // Check if explicit length has been provided
                    if(array_key_exists("length", $column->attributes)) {
                        $sqlTable   .=  sprintf("(%d)", $column->attributes["length"]);
                    }
                } elseif($this->dbDriver    === "sqlite") {
                    // String types in SQLite
                    $sqlTable   .=  "TEXT";
                }
            } elseif($column->type    === "text") {
                // Data type TEXT
                if($this->dbDriver  === "mysql") {
                    // MySQL
                    $textTable  =   [
                        self::TEXT_DEFAULT  =>   "TEXT",
                        self::TEXT_MEDIUM   =>   "MEDIUMTEXT",
                        self::TEXT_LONG =>   "LONGTEXT"
                    ];

                    $sqlTable   .=  $textTable[$column->flag];
                } elseif($this->dbDriver    === "sqlite") {
                    // SQLite
                    $sqlTable   .=  "TEXT";
                }
            } elseif($column->type    === "enum") {
                // Enums
                $options    =   array_key_exists("options", $column->attributes) ? (array) $column->attributes["options"] : [];
                if($this->dbDriver  === "mysql") {
                    // Straight-forward enum implementation in MySQL
                    $sqlTable   .=  "enum(";
                    $sqlTable   .=  implode(",", array_map(function($opt) {
                        return "'" . $opt . "'";
                    }, $options));
                    $sqlTable   .=  ")";
                } elseif($this->dbDriver    === "sqlite") {
                    // For SQLite we will use CHECK() on data type TEXT
                    $sqlTable   .=  sprintf("TEXT CHECK(%s in (", $name);
                    $sqlTable   .=  implode(",", array_map(function($opt) {
                        return "'" . $opt . "'";
                    }, $options));
                    $sqlTable   .=  "))";
                }
            } elseif($column->scalarType    === "double") {
                // Double and decimals
                if($this->dbDriver  === "mysql") {
                    // MySQL
                    $sqlTable   .=  $column->type;
                    if(array_key_exists("m", $column->attributes)) {
                        $sqlTable   .=  "(" . $column->attributes["m"];
                        if(array_key_exists("d", $column->attributes)) {
                            $sqlTable   .=  "," . $column->attributes["d"];
                        }

                        $sqlTable   .=   ")";
                    }
                } elseif($this->dbDriver    === "sqlite") {
                    // SQLite Real
                    $sqlTable   .=  "REAL";
                }
            }

            // Unsigned Number?
            if(array_key_exists("signed", $column->attributes)  &&  $column->attributes["signed"]   === 0) {
                // Is this attribute appropriate for this column type?
                if(in_array($column->scalarType, ["integer","double"])) {
                    if($this->dbDriver  === "sqlite"    &&  array_key_exists("ai", $column->attributes)) {
                        // SQLite AI columns cannot have UNSIGNED declaration
                    } else {
                        $sqlTable   .=  " UNSIGNED";
                    }
                }
            }

            // Primary Key
            if(array_key_exists("primary", $column->attributes)) {
                $sqlTable   .=  " PRIMARY KEY";
            }

            // Auto-increment
            if(array_key_exists("ai", $column->attributes)) {
                // Is this attribute appropriate for this column type?
                if(in_array($column->scalarType, ["integer","double"])) {
                    if($this->dbDriver  === "mysql") {
                        $sqlTable   .= " auto_increment";
                    } elseif($this->dbDriver    === "sqlite") {
                        $sqlTable   .=  " AUTOINCREMENT";

                        // AUTOINCREMENT must be used with INTEGER PRIMARY KEY
                        if(!array_key_exists("primary", $column->attributes)) {
                            throw SchemaException::columnParseError("AUTOINCREMENT must be used with INTEGER PRIMARY KEY");
                        }
                    }
                }
            }

            // Unique
            if(array_key_exists("unique", $column->attributes)) {
                if($this->dbDriver  === "mysql") {
                    // Save UNIQUE flag in an Array to be processed after all columns
                    $mySqlUniqueKeys[]  =   $name;
                } elseif ($this->dbDriver    === "sqlite") {
                    // Add UNIQUE flag inline
                    $sqlTable   .=  " UNIQUE";
                }
            }

            // Special attributes
            // MySQL charset and collation
            if($this->dbDriver  === "mysql") {
                if($column->scalarType  === "string") {
                    $sqlTable   .=  " CHARACTER SET " . $column->attributes["charset"];
                    $sqlTable   .=  " COLLATE " . $column->attributes["collation"];
                }
            }

            // Is Nullable?
            if(!array_key_exists("nullable", $column->attributes)) {
                // Cannot be a NULL
                $sqlTable   .=  " NOT NULL";
            }

            // Default Value
            if($column->default === null) {
                // Default Value is NULL, But column is?
                if(array_key_exists("nullable", $column->attributes)) {
                    $sqlTable   .=  " default NULL";
                }
            } else {
                // Set default value
                $sqlTable   .=  " default ";
                $sqlTable   .=  (is_string($column->default)) ? sprintf("'%s'", $column->default) : $column->default;
            }

            // End column line
            $sqlTable   .=  "," . $lb;
        }

        // MySQL specific constrains and additional columns
        if($this->dbDriver  === "mysql") {
            // Unique Keys
            if(count($mySqlUniqueKeys)  >   0) {
                foreach($mySqlUniqueKeys as $uniqueKey) {
                    $sqlTable   .=  sprintf("%sUNIQUE KEY (`%s`),%s", $indent, $uniqueKey, $lb);
                }
            }
        }

        // Constraints
        if(count($this->constraints)    >   0) {
            foreach($this->constraints as $name => $constraint) {
                // Given indent
                $sqlTable   .=  $indent;

                // Constraints for different database types
                if($this->dbDriver  === "mysql") {
                    // Check constraint type
                    if($constraint["type"]  === "unique") {
                        // Unique Constraint
                        $sqlTable   .=  sprintf(
                            "UNIQUE KEY `%s` (%s),%s",
                            $name,
                            implode(",", array_map(function($col) {
                                return "`" . $col . "`";
                            }, $constraint["cols"])),
                            $lb
                        );
                    } elseif($constraint["type"]    === "foreign") {
                        // Foreign Constraint
                        $sqlTable   .=  sprintf(
                            "FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`),%s",
                            $name,
                            $constraint["table"],
                            $constraint["col"],
                            $lb
                        );
                    }
                } elseif($this->dbDriver    === "sqlite") {
                    // Check constraint type
                    if($constraint["type"]  === "unique") {
                        // Unique Constraint
                        $sqlTable   .=  sprintf(
                            "CONSTRAINT `%s` UNIQUE (%s),%s",
                            $name,
                            implode(",", array_map(function($col) {
                                return "`" . $col . "`";
                            }, $constraint["cols"])),
                            $lb
                        );
                    } elseif($constraint["type"]    === "foreign") {
                        // Foreign Constraint
                        $sqlTable   .=  sprintf(
                            "CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s`(`%s`),%s",
                            "cnstrnt_" . $name . "_frgn",
                            $name,
                            $constraint["table"],
                            $constraint["col"],
                            $lb
                        );
                    }
                }
            }
        }

        // Closing CREATE TABLE statement
        $sqlTable   =   substr($sqlTable, 0, -1 * (1+strlen($lb))) . $lb;
        if($this->dbDriver  === "mysql") {
            // Specify storage engine
            $sqlTable   .=  sprintf(") ENGINE=%s;", $this->tableEngine);
        } else {
            $sqlTable   .=  ");";
        }

        return $sqlTable;
    }
}