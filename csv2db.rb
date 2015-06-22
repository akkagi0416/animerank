#!/usr/bin/env ruby

#
# 複数のcsv(Excel)を1つのdb(sqlite3)へ変換
# csv1 csv2 => db
#
# $ ./csv2db.rb aiu.csv aiu2.csv
#
# --- csv data format ---
# mvno_list,日本語カラム名,...
# id,shortname,...
# 1,ocn,...
# 2,iijmio,...
#

require 'sqlite3'
require 'csv'

db_fname  = 'animerank.db'    # データベースファイル名

if File.exist?( db_fname )
    old = File.basename( db_fname, '.*' ) + Time.now.strftime("_%Y%m%d%H%M%S") + File.extname( db_fname )
    File.rename( db_fname, old )
end
$db = SQLite3::Database.new( db_fname )

def create_table( tbname, row )
    sql = "CREATE TABLE #{tbname}(%COLUMN%)"
    column = ''
    length = row.length
    row.each_with_index do |c, i|
        column += "\n\t"
        if c =~ /^(id$|id_)/
            column += c + ' INTEGER,'
            next
        end
        column += c + ' TEXT'
        if i != length - 1
            column += ','
        end
    end
    sql = sql.sub(/%COLUMN%/, column)

    unless $db.execute('select tbl_name from sqlite_master').flatten.include?(tbname)
        $db.execute(sql)
    end
end

def insert_into( tbname, row )
    sql = "INSERT INTO #{tbname} VALUES(%VALUES%)"
    # '#{c}',で要素を連結 & ' -> '' へエスケープ処理
    values = ''
    row.each do |c|
        if c != nil
            c = c.gsub( /'/, "''" );
        end
        values += "'#{c}',"
    end
    values.sub!(/,$/, '')

    sql = sql.sub(/%VALUES%/, values )
    $db.execute( sql )
end

#open(fname, "r:Windows-31J:UTF-8", undef: :replace) do |f|
def csv2db(fname)
    open(fname, "r:Windows-31J:UTF-8") do |f|
        tbname = ""
        CSV.new(f).each_with_index do |row, i|
            if i == 0           # テーブル名だけ取得
                tbname = row[0]
            elsif i == 1        # columnを取得
                create_table( tbname, row )
            else                # 各行をdbへ記録
                insert_into( tbname, row )
            end
        end
    end
end

ARGV.each do |fname|
    if File.extname( fname ) =~ /\.csv/i
        puts fname
        csv2db( fname )
    end
end

$db.close

