<?php

namespace App\Filament\Resources\Blogs\Schemas;

use App\Models\BlogCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make()
                    ->tabs([

                        // ── Tab 1: Write ─────────────────────────────────
                        Tab::make('Write')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) =>
                                        $set('slug', Str::slug($state))
                                    )
                                    ->placeholder('Enter article title...')
                                    ->columnSpanFull(),

                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->prefix('blog/')
                                    ->helperText('Auto-generated from title. Edit to customise the URL.')
                                    ->columnSpanFull(),

                                Textarea::make('excerpt')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->placeholder('A short summary shown on the blog listing page...')
                                    ->helperText('Max 500 characters.')
                                    ->columnSpanFull(),

                                RichEditor::make('content')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline', 'strike',
                                        'h2', 'h3',
                                        'bulletList', 'orderedList',
                                        'blockquote', 'codeBlock',
                                        'attachFiles',
                                        'link', 'redo', 'undo',
                                    ])
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('blog-content')
                                    ->fileAttachmentsVisibility('public')
                                    ->placeholder('Start writing your article...')
                                    ->columnSpanFull(),
                            ]),

                        // ── Tab 2: Settings ──────────────────────────────
                        Tab::make('Settings')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make('Publishing')
                                    ->description('Control when and how this article is published.')
                                    ->schema([
                                        Select::make('category_id')
                                            ->label('Category')
                                            ->options(BlogCategory::orderBy('name')->pluck('name', 'id'))
                                            ->searchable()
                                            ->native(false)
                                            ->placeholder('Select a category')
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($state, callable $set) =>
                                                        $set('slug', Str::slug($state))
                                                    ),
                                                TextInput::make('slug')->required(),
                                            ])
                                            ->createOptionUsing(fn (array $data) =>
                                                BlogCategory::create($data)->id
                                            ),

                                        DateTimePicker::make('published_at')
                                            ->label('Publish Date')
                                            ->helperText('Leave empty to save as a draft.')
                                            ->native(false),
                                    ]),

                                Section::make('Cover Image')
                                    ->description('This image appears at the top of the article and in social previews.')
                                    ->schema([
                                        FileUpload::make('cover_image')
                                            ->label(false)
                                            ->image()
                                            ->directory('blog-covers')
                                            ->imageEditor()
                                            ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                                            ->maxSize(4096)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 3: Outline ───────────────────────────────
                        Tab::make('Outline')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Section::make('Table of Contents')
                                    ->description('Define the sections that appear as a clickable Table of Contents at the top of the article.')
                                    ->schema([
                                        Repeater::make('outline')
                                            ->label(false)
                                            ->schema([
                                                TextInput::make('title')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($state, callable $set) =>
                                                        $set('anchor', Str::slug($state))
                                                    )
                                                    ->placeholder('Section heading text...')
                                                    ->columnSpan(1),

                                                TextInput::make('anchor')
                                                    ->required()
                                                    ->prefix('#')
                                                    ->helperText('Auto-generated from title. Must match the heading ID in content.')
                                                    ->placeholder('section-anchor')
                                                    ->columnSpan(1),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(0)
                                            ->addActionLabel('Add section')
                                            ->reorderable()
                                            ->collapsible()
                                            ->cloneable()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 4: SEO ───────────────────────────────────
                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('Search Engine Optimisation')
                                    ->description('Customise how this article appears in search engine results.')
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label('Meta Title')
                                            ->maxLength(70)
                                            ->helperText('Recommended: 50–70 characters.')
                                            ->columnSpanFull(),

                                        Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->rows(3)
                                            ->maxLength(160)
                                            ->helperText('Recommended: 120–160 characters.')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ]);
    }
}
