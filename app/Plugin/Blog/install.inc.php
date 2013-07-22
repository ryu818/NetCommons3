version = "3.0.0.0"
controller_action = "blog"
edit_controller_action = "blog/edits"
style_controller_action = "blog/styles"
default_enable_flag = 1
; 最初はdisableのコンテンツにしておき、コンテンツ一覧には表示させない。
; ブロック追加->コンテンツ一覧->既存のコンテンツに置換の処理をスムーズにするため
add_block_disable = 1
; モジュール操作を可能にするかどうか
; enable   使用可能だがデフォルト使用不可(システム管理より変更可)
; enabled  使用可能
; disabled 使用不可
copy_operation = "enabled"
shortcut_operation = "enable"
move_operation = "enabled"