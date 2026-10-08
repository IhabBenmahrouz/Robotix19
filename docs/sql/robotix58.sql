-- Sauvegarde de la base Robotix58 générée le 06/10/2026 17:42 (SMO)
-- Structure, données, procédures stockées, déclencheurs et vues
-- Restauration : créer une base vide, la sélectionner, puis exécuter ce script
GO

/****** Object:  Table [dbo].[Categorie]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Categorie](
	[idCategorie] [int] IDENTITY(1,1) NOT NULL,
	[libelle] [varchar](80) COLLATE French_CI_AS NOT NULL,
	[description] [varchar](max) COLLATE French_CI_AS NOT NULL,
 CONSTRAINT [PK_Categorie] PRIMARY KEY CLUSTERED 
(
	[idCategorie] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CategorieAge]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CategorieAge](
	[idCategorieAge] [int] IDENTITY(1,1) NOT NULL,
	[libelle] [varchar](30) COLLATE French_CI_AS NOT NULL,
	[ageMin] [int] NOT NULL,
	[ageMax] [int] NOT NULL,
 CONSTRAINT [PK_CategorieAge] PRIMARY KEY CLUSTERED 
(
	[idCategorieAge] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[HistoriqueAdhesion]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[HistoriqueAdhesion](
	[idHistorique] [int] IDENTITY(1,1) NOT NULL,
	[idUtilisateur] [int] NOT NULL,
	[nom] [varchar](50) COLLATE French_CI_AS NULL,
	[prenom] [varchar](50) COLLATE French_CI_AS NULL,
	[derniereAnnee] [int] NULL,
	[motif] [varchar](100) COLLATE French_CI_AS NOT NULL,
	[dateHistorisation] [datetime] NOT NULL,
 CONSTRAINT [PK_HistoriqueAdhesion] PRIMARY KEY CLUSTERED 
(
	[idHistorique] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[MailAEnvoyer]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[MailAEnvoyer](
	[idMail] [int] IDENTITY(1,1) NOT NULL,
	[destinataire] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[objet] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[corps] [varchar](2000) COLLATE French_CI_AS NOT NULL,
	[dateCreation] [datetime] NOT NULL,
	[envoye] [bit] NOT NULL,
 CONSTRAINT [PK_MailAEnvoyer] PRIMARY KEY CLUSTERED 
(
	[idMail] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Marque]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Marque](
	[idMarque] [int] IDENTITY(1,1) NOT NULL,
	[nom] [varchar](80) COLLATE French_CI_AS NOT NULL,
	[pays] [varchar](60) COLLATE French_CI_AS NOT NULL,
	[siteWeb] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[logo] [varchar](255) COLLATE French_CI_AS NOT NULL,
	[description] [varchar](max) COLLATE French_CI_AS NOT NULL,
	[ticker] [varchar](10) COLLATE French_CI_AS NULL,
	[placeMarche] [varchar](30) COLLATE French_CI_AS NULL,
 CONSTRAINT [PK_Marque] PRIMARY KEY CLUSTERED 
(
	[idMarque] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[migrations]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[migrations](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[version] [varchar](255) COLLATE French_CI_AS NOT NULL,
	[class] [varchar](255) COLLATE French_CI_AS NOT NULL,
	[group] [varchar](255) COLLATE French_CI_AS NOT NULL,
	[namespace] [varchar](255) COLLATE French_CI_AS NOT NULL,
	[time] [int] NOT NULL,
	[batch] [int] NOT NULL,
 CONSTRAINT [pk_migrations] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Produit]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Produit](
	[idProduit] [int] IDENTITY(1,1) NOT NULL,
	[reference] [varchar](40) COLLATE French_CI_AS NOT NULL,
	[nom] [varchar](120) COLLATE French_CI_AS NOT NULL,
	[description] [varchar](max) COLLATE French_CI_AS NOT NULL,
	[prixHt] [decimal](10, 2) NOT NULL,
	[stock] [int] NOT NULL,
	[actif] [bit] NOT NULL,
	[dateAjout] [date] NOT NULL,
	[tauxTva] [decimal](4, 2) NOT NULL,
	[idCategorie] [int] NOT NULL,
	[idMarque] [int] NOT NULL,
 CONSTRAINT [PK_Produit] PRIMARY KEY CLUSTERED 
(
	[idProduit] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Reduction]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Reduction](
	[idReduction] [int] IDENTITY(1,1) NOT NULL,
	[idCategorieAge] [int] NOT NULL,
	[txReduction] [decimal](5, 2) NOT NULL,
 CONSTRAINT [PK_Reduction] PRIMARY KEY CLUSTERED 
(
	[idReduction] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Showroom]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Showroom](
	[idShowroom] [int] IDENTITY(1,1) NOT NULL,
	[nom] [varchar](100) COLLATE French_CI_AS NOT NULL,
	[adresse] [varchar](200) COLLATE French_CI_AS NOT NULL,
	[codePostal] [varchar](5) COLLATE French_CI_AS NOT NULL,
	[ville] [varchar](100) COLLATE French_CI_AS NOT NULL,
	[latitude] [decimal](9, 6) NOT NULL,
	[longitude] [decimal](9, 6) NOT NULL,
	[telephone] [varchar](20) COLLATE French_CI_AS NULL,
	[horaires] [varchar](200) COLLATE French_CI_AS NULL,
	[image] [varchar](255) COLLATE French_CI_AS NULL,
 CONSTRAINT [PK_Showroom] PRIMARY KEY CLUSTERED 
(
	[idShowroom] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Tarif]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Tarif](
	[idTarif] [int] IDENTITY(1,1) NOT NULL,
	[code] [varchar](30) COLLATE French_CI_AS NOT NULL,
	[libelle] [varchar](80) COLLATE French_CI_AS NOT NULL,
	[famille] [varchar](10) COLLATE French_CI_AS NOT NULL,
	[mode] [varchar](12) COLLATE French_CI_AS NOT NULL,
	[valeur] [decimal](10, 2) NOT NULL,
	[description] [varchar](300) COLLATE French_CI_AS NULL,
	[actif] [bit] NOT NULL,
 CONSTRAINT [PK_Tarif] PRIMARY KEY CLUSTERED 
(
	[idTarif] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Thematique]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Thematique](
	[idThematique] [int] IDENTITY(1,1) NOT NULL,
	[libelle] [varchar](80) COLLATE French_CI_AS NOT NULL,
 CONSTRAINT [PK_Thematique] PRIMARY KEY CLUSTERED 
(
	[idThematique] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TypeEvenement]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TypeEvenement](
	[code] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[libelle] [varchar](40) COLLATE French_CI_AS NOT NULL,
 CONSTRAINT [PK_TypeEvenement] PRIMARY KEY CLUSTERED 
(
	[code] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Utilisateur]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Utilisateur](
	[idUtilisateur] [int] IDENTITY(1,1) NOT NULL,
	[nom] [varchar](50) COLLATE French_CI_AS NOT NULL,
	[prenom] [varchar](50) COLLATE French_CI_AS NOT NULL,
	[email] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[motDePasse] [varchar](255) COLLATE French_CI_AS NOT NULL,
	[dateInscription] [datetime] NOT NULL,
	[actif] [bit] NOT NULL,
	[pseudo] [varchar](30) COLLATE French_CI_AS NULL,
 CONSTRAINT [PK_Utilisateur] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Administrateur]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Administrateur](
	[idUtilisateur] [int] NOT NULL,
	[dateNomination] [date] NOT NULL,
 CONSTRAINT [PK_Administrateur] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Client]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Client](
	[idUtilisateur] [int] NOT NULL,
	[telephone] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[dateNaissance] [date] NULL,
	[photo] [varchar](255) COLLATE French_CI_AS NULL,
	[idCategorieAge] [int] NULL,
 CONSTRAINT [PK_Client] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ClientInteret]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ClientInteret](
	[idUtilisateur] [int] NOT NULL,
	[idCategorie] [int] NOT NULL,
 CONSTRAINT [PK_ClientInteret] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC,
	[idCategorie] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[CompatibiliteProduit]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[CompatibiliteProduit](
	[idProduit1] [int] NOT NULL,
	[idProduit2] [int] NOT NULL,
 CONSTRAINT [PK_CompatibiliteProduit] PRIMARY KEY CLUSTERED 
(
	[idProduit1] ASC,
	[idProduit2] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Image]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Image](
	[idImage] [int] IDENTITY(1,1) NOT NULL,
	[fichier] [varchar](255) COLLATE French_CI_AS NOT NULL,
	[legende] [varchar](150) COLLATE French_CI_AS NULL,
	[numOrdre] [int] NOT NULL,
	[idProduit] [int] NOT NULL,
 CONSTRAINT [PK_Image] PRIMARY KEY CLUSTERED 
(
	[idImage] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Journal]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Journal](
	[idJournal] [int] IDENTITY(1,1) NOT NULL,
	[dateAction] [datetime] NOT NULL,
	[typeAction] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[tableCible] [varchar](60) COLLATE French_CI_AS NOT NULL,
	[description] [varchar](500) COLLATE French_CI_AS NOT NULL,
	[idCible] [int] NOT NULL,
	[idUtilisateur] [int] NOT NULL,
 CONSTRAINT [PK_Journal] PRIMARY KEY CLUSTERED 
(
	[idJournal] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Membre]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Membre](
	[idUtilisateur] [int] NOT NULL,
	[niveau] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[nbEvenements] [int] NOT NULL,
 CONSTRAINT [PK_Membre] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[MessageContact]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[MessageContact](
	[idMessage] [int] IDENTITY(1,1) NOT NULL,
	[nom] [varchar](100) COLLATE French_CI_AS NOT NULL,
	[email] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[telephone] [varchar](20) COLLATE French_CI_AS NULL,
	[objet] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[idProduit] [int] NULL,
	[message] [varchar](2000) COLLATE French_CI_AS NOT NULL,
	[dateEnvoi] [datetime] NOT NULL,
	[traite] [bit] NOT NULL,
 CONSTRAINT [PK_MessageContact] PRIMARY KEY CLUSTERED 
(
	[idMessage] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Redacteur]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Redacteur](
	[idUtilisateur] [int] NOT NULL,
	[pseudo] [varchar](60) COLLATE French_CI_AS NOT NULL,
	[biographie] [varchar](max) COLLATE French_CI_AS NULL,
 CONSTRAINT [PK_Redacteur] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Reunion]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Reunion](
	[idReunion] [int] IDENTITY(1,1) NOT NULL,
	[dateReunion] [datetime] NOT NULL,
	[objet] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[idShowroom] [int] NULL,
 CONSTRAINT [PK_Reunion] PRIMARY KEY CLUSTERED 
(
	[idReunion] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Suivre]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Suivre](
	[idUtilisateur] [int] NOT NULL,
	[idMarque] [int] NOT NULL,
	[dateSuivi] [date] NOT NULL,
 CONSTRAINT [PK_Suivre] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC,
	[idMarque] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Adhesion]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Adhesion](
	[idAdhesion] [int] IDENTITY(1,1) NOT NULL,
	[idUtilisateur] [int] NOT NULL,
	[annee] [int] NOT NULL,
	[dateAdhesion] [datetime] NOT NULL,
	[idTarif] [int] NOT NULL,
	[montant] [decimal](10, 2) NOT NULL,
 CONSTRAINT [PK_Adhesion] PRIMARY KEY CLUSTERED 
(
	[idAdhesion] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Adresse]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Adresse](
	[idAdresse] [int] IDENTITY(1,1) NOT NULL,
	[libelle] [varchar](60) COLLATE French_CI_AS NOT NULL,
	[ligne1] [varchar](120) COLLATE French_CI_AS NOT NULL,
	[ligne2] [varchar](120) COLLATE French_CI_AS NULL,
	[codePostal] [varchar](10) COLLATE French_CI_AS NOT NULL,
	[ville] [varchar](80) COLLATE French_CI_AS NOT NULL,
	[pays] [varchar](50) COLLATE French_CI_AS NOT NULL,
	[idUtilisateur] [int] NOT NULL,
 CONSTRAINT [PK_Adresse] PRIMARY KEY CLUSTERED 
(
	[idAdresse] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Animateur]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Animateur](
	[idUtilisateur] [int] NOT NULL,
	[specialite] [varchar](80) COLLATE French_CI_AS NOT NULL,
	[idRemplacant] [int] NULL,
 CONSTRAINT [PK_Animateur] PRIMARY KEY CLUSTERED 
(
	[idUtilisateur] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Article]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Article](
	[idArticle] [int] IDENTITY(1,1) NOT NULL,
	[titre] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[slug] [varchar](180) COLLATE French_CI_AS NOT NULL,
	[chapo] [varchar](500) COLLATE French_CI_AS NULL,
	[contenu] [varchar](max) COLLATE French_CI_AS NOT NULL,
	[imageUne] [varchar](255) COLLATE French_CI_AS NULL,
	[datePublication] [datetime] NULL,
	[estPublie] [bit] NOT NULL,
	[idMarque] [int] NULL,
	[idUtilisateur] [int] NOT NULL,
 CONSTRAINT [PK_Article] PRIMARY KEY CLUSTERED 
(
	[idArticle] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Commande]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Commande](
	[idCommande] [int] IDENTITY(1,1) NOT NULL,
	[dateCommande] [datetime] NOT NULL,
	[statutCourant] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[montantTotal] [decimal](10, 2) NOT NULL,
	[idAdresseLivraison] [int] NOT NULL,
	[idAdresseFacturation] [int] NULL,
	[idUtilisateur] [int] NOT NULL,
 CONSTRAINT [PK_Commande] PRIMARY KEY CLUSTERED 
(
	[idCommande] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Contenir]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Contenir](
	[idProduit] [int] NOT NULL,
	[idCommande] [int] NOT NULL,
	[quantite] [int] NOT NULL,
	[prixUnitaireHt] [decimal](10, 2) NOT NULL,
	[tauxTva] [decimal](4, 2) NOT NULL,
 CONSTRAINT [PK_Contenir] PRIMARY KEY CLUSTERED 
(
	[idProduit] ASC,
	[idCommande] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Convocation]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Convocation](
	[idReunion] [int] NOT NULL,
	[idAnimateur] [int] NOT NULL,
	[dateEnvoi] [datetime] NOT NULL,
 CONSTRAINT [PK_Convocation] PRIMARY KEY CLUSTERED 
(
	[idReunion] ASC,
	[idAnimateur] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Evenement]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Evenement](
	[idEvenement] [int] IDENTITY(1,1) NOT NULL,
	[titre] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[description] [varchar](1000) COLLATE French_CI_AS NULL,
	[type] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[dateDebut] [datetime] NOT NULL,
	[dateFin] [datetime] NOT NULL,
	[idShowroom] [int] NULL,
	[idProduit] [int] NULL,
	[nbPlaces] [int] NOT NULL,
	[idAnimateur] [int] NULL,
 CONSTRAINT [PK_Evenement] PRIMARY KEY CLUSTERED 
(
	[idEvenement] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[HistoriqueStatut]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[HistoriqueStatut](
	[idHistorique] [int] IDENTITY(1,1) NOT NULL,
	[dateAction] [datetime] NOT NULL,
	[ancienStatut] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[nouveauStatut] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[idCommande] [int] NOT NULL,
 CONSTRAINT [PK_HistoriqueStatut] PRIMARY KEY CLUSTERED 
(
	[idHistorique] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Inscription]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Inscription](
	[idMembre] [int] NOT NULL,
	[idEvenement] [int] NOT NULL,
	[dateInscription] [datetime] NOT NULL,
	[present] [bit] NULL,
	[travailRealise] [varchar](300) COLLATE French_CI_AS NULL,
 CONSTRAINT [PK_Inscription] PRIMARY KEY CLUSTERED 
(
	[idMembre] ASC,
	[idEvenement] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Mentionner]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Mentionner](
	[idProduit] [int] NOT NULL,
	[idArticle] [int] NOT NULL,
 CONSTRAINT [PK_Mentionner] PRIMARY KEY CLUSTERED 
(
	[idProduit] ASC,
	[idArticle] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Paiement]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Paiement](
	[idPaiement] [int] IDENTITY(1,1) NOT NULL,
	[mode] [varchar](30) COLLATE French_CI_AS NOT NULL,
	[montant] [decimal](10, 2) NOT NULL,
	[datePaiement] [datetime] NOT NULL,
	[statutPaiement] [varchar](20) COLLATE French_CI_AS NOT NULL,
	[referenceTransaction] [varchar](60) COLLATE French_CI_AS NOT NULL,
	[idCommande] [int] NOT NULL,
 CONSTRAINT [PK_Paiement] PRIMARY KEY CLUSTERED 
(
	[idPaiement] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Panier]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Panier](
	[idPanier] [int] IDENTITY(1,1) NOT NULL,
	[idEvenement] [int] NOT NULL,
	[idUtilisateur] [int] NOT NULL,
	[nomEvenement] [varchar](150) COLLATE French_CI_AS NOT NULL,
	[dateResa] [datetime] NOT NULL,
	[nbPlace] [int] NOT NULL,
 CONSTRAINT [PK_Panier] PRIMARY KEY CLUSTERED 
(
	[idPanier] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PointOrdreJour]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PointOrdreJour](
	[idReunion] [int] NOT NULL,
	[numOrdre] [int] NOT NULL,
	[libelle] [varchar](200) COLLATE French_CI_AS NOT NULL,
 CONSTRAINT [PK_PointOrdreJour] PRIMARY KEY CLUSTERED 
(
	[idReunion] ASC,
	[numOrdre] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Aborder]    Script Date: 06/10/2026 17:43:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Aborder](
	[idArticle] [int] NOT NULL,
	[idThematique] [int] NOT NULL,
 CONSTRAINT [PK_Aborder] PRIMARY KEY CLUSTERED 
(
	[idArticle] ASC,
	[idThematique] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
SET IDENTITY_INSERT [dbo].[Categorie] ON 

INSERT [dbo].[Categorie] ([idCategorie], [libelle], [description]) VALUES (393, N'Compagnon', N'Des robots de présence et de conversation pour toute la famille.')
INSERT [dbo].[Categorie] ([idCategorie], [libelle], [description]) VALUES (394, N'Domestique', N'Des robots qui rangent, portent et assistent dans les tâches du quotidien.')
INSERT [dbo].[Categorie] ([idCategorie], [libelle], [description]) VALUES (395, N'Éducatif', N'Des robots programmables pour apprendre la robotique et l''intelligence artificielle.')
INSERT [dbo].[Categorie] ([idCategorie], [libelle], [description]) VALUES (396, N'Premium', N'Les humanoïdes les plus avancés, pour les passionnés exigeants.')
SET IDENTITY_INSERT [dbo].[Categorie] OFF
GO
SET IDENTITY_INSERT [dbo].[CategorieAge] ON 

INSERT [dbo].[CategorieAge] ([idCategorieAge], [libelle], [ageMin], [ageMax]) VALUES (175, N'Jeune', 18, 24)
INSERT [dbo].[CategorieAge] ([idCategorieAge], [libelle], [ageMin], [ageMax]) VALUES (176, N'Adulte', 25, 59)
INSERT [dbo].[CategorieAge] ([idCategorieAge], [libelle], [ageMin], [ageMax]) VALUES (177, N'Senior', 60, 120)
SET IDENTITY_INSERT [dbo].[CategorieAge] OFF
GO
SET IDENTITY_INSERT [dbo].[MailAEnvoyer] ON 

INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (1, N'client@robotix.test', N'Inscription confirmée : Atelier découverte Reachy Mini', N'Bonjour Camille, votre inscription à « Atelier découverte Reachy Mini » le 09/09/2026 à 18:30 (Robotix Marseille Vieux-Port) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.513' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (2, N'lucas.bernard@exemple.fr', N'Inscription confirmée : Atelier découverte Reachy Mini', N'Bonjour Lucas, votre inscription à « Atelier découverte Reachy Mini » le 09/09/2026 à 18:30 (Robotix Marseille Vieux-Port) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.590' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (3, N'emma.petit@exemple.fr', N'Inscription confirmée : Atelier découverte Reachy Mini', N'Bonjour Emma, votre inscription à « Atelier découverte Reachy Mini » le 09/09/2026 à 18:30 (Robotix Marseille Vieux-Port) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.697' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (4, N'manon.leroy@exemple.fr', N'Inscription confirmée : Atelier découverte Reachy Mini', N'Bonjour Manon, votre inscription à « Atelier découverte Reachy Mini » le 09/09/2026 à 18:30 (Robotix Marseille Vieux-Port) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.713' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (5, N'client@robotix.test', N'Inscription confirmée : Démonstration de rentrée Figure 02', N'Bonjour Camille, votre inscription à « Démonstration de rentrée Figure 02 » le 16/09/2026 à 14:00 (Robotix Paris Opéra) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.717' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (6, N'louis.durand@exemple.fr', N'Inscription confirmée : Démonstration de rentrée Figure 02', N'Bonjour Louis, votre inscription à « Démonstration de rentrée Figure 02 » le 16/09/2026 à 14:00 (Robotix Paris Opéra) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.717' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (7, N'helene.michel@exemple.fr', N'Inscription confirmée : Démonstration de rentrée Figure 02', N'Bonjour Hélène, votre inscription à « Démonstration de rentrée Figure 02 » le 16/09/2026 à 14:00 (Robotix Paris Opéra) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.720' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (8, N'lucas.bernard@exemple.fr', N'Inscription confirmée : Atelier programmation Unitree R1', N'Bonjour Lucas, votre inscription à « Atelier programmation Unitree R1 » le 23/09/2026 à 18:00 (Robotix Lyon Part-Dieu) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.720' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (9, N'emma.petit@exemple.fr', N'Inscription confirmée : Atelier programmation Unitree R1', N'Bonjour Emma, votre inscription à « Atelier programmation Unitree R1 » le 23/09/2026 à 18:00 (Robotix Lyon Part-Dieu) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.723' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (10, N'nathan.robert@exemple.fr', N'Inscription confirmée : Atelier programmation Unitree R1', N'Bonjour Nathan, votre inscription à « Atelier programmation Unitree R1 » le 23/09/2026 à 18:00 (Robotix Lyon Part-Dieu) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.723' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (11, N'sarah.lefebvre@exemple.fr', N'Inscription confirmée : Atelier programmation Unitree R1', N'Bonjour Sarah, votre inscription à « Atelier programmation Unitree R1 » le 23/09/2026 à 18:00 (Robotix Lyon Part-Dieu) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.727' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (12, N'client@robotix.test', N'Inscription confirmée : Atelier programmation Reachy Mini', N'Bonjour Camille, votre inscription à « Atelier programmation Reachy Mini » le 21/10/2026 à 18:30 (Robotix Marseille Vieux-Port) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.730' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (13, N'manon.leroy@exemple.fr', N'Inscription confirmée : Atelier programmation Reachy Mini', N'Bonjour Manon, votre inscription à « Atelier programmation Reachy Mini » le 21/10/2026 à 18:30 (Robotix Marseille Vieux-Port) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.730' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (14, N'sarah.lefebvre@exemple.fr', N'Inscription confirmée : Atelier : apprendre un geste à Reachy 2', N'Bonjour Sarah, votre inscription à « Atelier : apprendre un geste à Reachy 2 » le 12/11/2026 à 18:30 (Robotix Paris Opéra) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.733' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (15, N'nathan.robert@exemple.fr', N'Inscription confirmée : Atelier : apprendre un geste à Reachy 2', N'Bonjour Nathan, votre inscription à « Atelier : apprendre un geste à Reachy 2 » le 12/11/2026 à 18:30 (Robotix Paris Opéra) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.733' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (16, N'lucas.bernard@exemple.fr', N'Inscription confirmée : Atelier famille Unitree R1', N'Bonjour Lucas, votre inscription à « Atelier famille Unitree R1 » le 09/12/2026 à 18:30 (Robotix Lyon Part-Dieu) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.737' AS DateTime), 0)
INSERT [dbo].[MailAEnvoyer] ([idMail], [destinataire], [objet], [corps], [dateCreation], [envoye]) VALUES (17, N'chloe.richard@exemple.fr', N'Inscription confirmée : Atelier famille Unitree R1', N'Bonjour Chloé, votre inscription à « Atelier famille Unitree R1 » le 09/12/2026 à 18:30 (Robotix Lyon Part-Dieu) est confirmée. À bientôt au Club Robotix !', CAST(N'2026-10-05T17:34:55.737' AS DateTime), 0)
SET IDENTITY_INSERT [dbo].[MailAEnvoyer] OFF
GO
SET IDENTITY_INSERT [dbo].[Marque] ON 

INSERT [dbo].[Marque] ([idMarque], [nom], [pays], [siteWeb], [logo], [description], [ticker], [placeMarche]) VALUES (491, N'Unitree Robotics', N'Chine', N'https://www.unitree.com', N'unitree-robotics.svg', N'Pionnier chinois des robots à pattes et des humanoïdes accessibles.', NULL, NULL)
INSERT [dbo].[Marque] ([idMarque], [nom], [pays], [siteWeb], [logo], [description], [ticker], [placeMarche]) VALUES (492, N'Figure AI', N'États-Unis', N'https://www.figure.ai', N'figure-ai.svg', N'Start-up californienne spécialisée dans les humanoïdes polyvalents.', NULL, NULL)
INSERT [dbo].[Marque] ([idMarque], [nom], [pays], [siteWeb], [logo], [description], [ticker], [placeMarche]) VALUES (493, N'Agility Robotics', N'États-Unis', N'https://www.agilityrobotics.com', N'agility-robotics.svg', N'Concepteur de Digit, robot bipède pensé pour porter et ranger.', NULL, NULL)
INSERT [dbo].[Marque] ([idMarque], [nom], [pays], [siteWeb], [logo], [description], [ticker], [placeMarche]) VALUES (494, N'1X Technologies', N'Norvège', N'https://www.1x.tech', N'1x-technologies.svg', N'Fabricant norvégien de robots domestiques sûrs et légers.', NULL, NULL)
INSERT [dbo].[Marque] ([idMarque], [nom], [pays], [siteWeb], [logo], [description], [ticker], [placeMarche]) VALUES (495, N'Pollen Robotics', N'France', N'https://www.pollen-robotics.com', N'pollen-robotics.svg', N'Entreprise bordelaise à l''origine des robots open source Reachy.', NULL, NULL)
SET IDENTITY_INSERT [dbo].[Marque] OFF
GO
SET IDENTITY_INSERT [dbo].[migrations] ON 

INSERT [dbo].[migrations] ([id], [version], [class], [group], [namespace], [time], [batch]) VALUES (1, N'2026-10-01-000001', N'App\Database\Migrations\CreateShowroomEvenementContact', N'default', N'App', 1790870358, 1)
INSERT [dbo].[migrations] ([id], [version], [class], [group], [namespace], [time], [batch]) VALUES (2, N'2026-10-05-000001', N'App\Database\Migrations\CreateClubRobotix', N'default', N'App', 1791211936, 2)
INSERT [dbo].[migrations] ([id], [version], [class], [group], [namespace], [time], [batch]) VALUES (3, N'2026-10-05-000002', N'App\Database\Migrations\CreateClubAp3', N'default', N'App', 1791214271, 3)
INSERT [dbo].[migrations] ([id], [version], [class], [group], [namespace], [time], [batch]) VALUES (4, N'2026-10-05-000003', N'App\Database\Migrations\CreateProgrammationAp3', N'default', N'App', 1791214492, 4)
SET IDENTITY_INSERT [dbo].[migrations] OFF
GO
SET IDENTITY_INSERT [dbo].[Produit] ON 

INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (981, N'RBX-UNI-G1', N'Unitree G1', N'Compact (1,30 m) et agile, le G1 vous accueille, joue avec les enfants et apprend de nouveaux gestes par démonstration.', CAST(13900.00 AS Decimal(10, 2)), 8, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 393, 491)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (982, N'RBX-UNI-H1', N'Unitree H1', N'Humanoïde pleine taille (1,80 m) aux moteurs haute puissance : la référence pour les passionnés de robotique.', CAST(74900.00 AS Decimal(10, 2)), 2, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 396, 491)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (983, N'RBX-UNI-R1', N'Unitree R1', N'Le premier humanoïde à petit prix : idéal pour découvrir la programmation de mouvements en famille.', CAST(4900.00 AS Decimal(10, 2)), 15, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 395, 491)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (984, N'RBX-FIG-02', N'Figure 02', N'Mains à 16 degrés de liberté et dialogue naturel : Figure 02 range, plie le linge et charge le lave-vaisselle.', CAST(39900.00 AS Decimal(10, 2)), 4, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 394, 492)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (985, N'RBX-FIG-03', N'Figure 03', N'La nouvelle génération Figure : plus légère, plus silencieuse, avec une vision améliorée pour la maison.', CAST(59900.00 AS Decimal(10, 2)), 3, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 396, 492)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (986, N'RBX-AGI-DGT', N'Digit', N'Bipède robuste conçu pour porter des charges jusqu''à 16 kg : courses, cartons et rangement du garage.', CAST(49900.00 AS Decimal(10, 2)), 3, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 394, 493)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (987, N'RBX-1X-NEO', N'NEO Gamma', N'Doux, léger et silencieux, NEO Gamma est pensé pour vivre avec vous : ménage léger, rappels et compagnie.', CAST(16500.00 AS Decimal(10, 2)), 6, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 394, 494)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (988, N'RBX-1X-EVE', N'EVE', N'Robot à roues au buste humanoïde : il circule dans toute la maison et assure une présence rassurante.', CAST(22900.00 AS Decimal(10, 2)), 5, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 393, 494)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (989, N'RBX-POL-R2', N'Reachy 2', N'Robot open source français à deux bras, programmable en Python : parfait pour les lycées et les makers.', CAST(19900.00 AS Decimal(10, 2)), 6, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 395, 495)
INSERT [dbo].[Produit] ([idProduit], [reference], [nom], [description], [prixHt], [stock], [actif], [dateAjout], [tauxTva], [idCategorie], [idMarque]) VALUES (990, N'RBX-POL-MINI', N'Reachy Mini', N'Petit robot de bureau expressif et programmable, pour s''initier à l''IA dès 10 ans.', CAST(349.00 AS Decimal(10, 2)), 40, 1, CAST(N'2026-10-05' AS Date), CAST(20.00 AS Decimal(4, 2)), 395, 495)
SET IDENTITY_INSERT [dbo].[Produit] OFF
GO
SET IDENTITY_INSERT [dbo].[Reduction] ON 

INSERT [dbo].[Reduction] ([idReduction], [idCategorieAge], [txReduction]) VALUES (175, 175, CAST(15.00 AS Decimal(5, 2)))
INSERT [dbo].[Reduction] ([idReduction], [idCategorieAge], [txReduction]) VALUES (176, 176, CAST(0.00 AS Decimal(5, 2)))
INSERT [dbo].[Reduction] ([idReduction], [idCategorieAge], [txReduction]) VALUES (177, 177, CAST(10.00 AS Decimal(5, 2)))
SET IDENTITY_INSERT [dbo].[Reduction] OFF
GO
SET IDENTITY_INSERT [dbo].[Showroom] ON 

INSERT [dbo].[Showroom] ([idShowroom], [nom], [adresse], [codePostal], [ville], [latitude], [longitude], [telephone], [horaires], [image]) VALUES (295, N'Robotix Paris Opéra', N'12 boulevard Haussmann', N'75009', N'Paris', CAST(48.873400 AS Decimal(9, 6)), CAST(2.333500 AS Decimal(9, 6)), N'01 42 00 19 19', N'Du mardi au samedi, 10 h – 19 h', NULL)
INSERT [dbo].[Showroom] ([idShowroom], [nom], [adresse], [codePostal], [ville], [latitude], [longitude], [telephone], [horaires], [image]) VALUES (296, N'Robotix Lyon Part-Dieu', N'17 rue du Docteur Bouchut', N'69003', N'Lyon', CAST(45.761200 AS Decimal(9, 6)), CAST(4.856200 AS Decimal(9, 6)), N'04 72 00 19 19', N'Du mardi au samedi, 10 h – 19 h', NULL)
INSERT [dbo].[Showroom] ([idShowroom], [nom], [adresse], [codePostal], [ville], [latitude], [longitude], [telephone], [horaires], [image]) VALUES (297, N'Robotix Marseille Vieux-Port', N'1 quai du Port', N'13002', N'Marseille', CAST(43.296500 AS Decimal(9, 6)), CAST(5.369800 AS Decimal(9, 6)), N'04 91 00 19 19', N'Du mardi au samedi, 10 h – 19 h', NULL)
SET IDENTITY_INSERT [dbo].[Showroom] OFF
GO
SET IDENTITY_INSERT [dbo].[Tarif] ON 

INSERT [dbo].[Tarif] ([idTarif], [code], [libelle], [famille], [mode], [valeur], [description], [actif]) VALUES (407, N'decouverte', N'Découverte', N'formule', N'fixe', CAST(49.00 AS Decimal(10, 2)), N'Ateliers mensuels et newsletter du club.', 1)
INSERT [dbo].[Tarif] ([idTarif], [code], [libelle], [famille], [mode], [valeur], [description], [actif]) VALUES (408, N'passion', N'Passion', N'formule', N'fixe', CAST(99.00 AS Decimal(10, 2)), N'Ateliers illimités, 10 % sur les accessoires, priorité aux démonstrations.', 1)
INSERT [dbo].[Tarif] ([idTarif], [code], [libelle], [famille], [mode], [valeur], [description], [actif]) VALUES (409, N'premium', N'Premium', N'formule', N'fixe', CAST(199.00 AS Decimal(10, 2)), N'Avantages Passion + un week-end d''essai d''un robot à domicile chaque année.', 1)
INSERT [dbo].[Tarif] ([idTarif], [code], [libelle], [famille], [mode], [valeur], [description], [actif]) VALUES (410, N'garantie', N'Garantie étendue 3 ans', N'option', N'pourcentage', CAST(12.00 AS Decimal(10, 2)), N'12 % du prix HT du robot : pièces, main-d''œuvre et robot de prêt.', 1)
INSERT [dbo].[Tarif] ([idTarif], [code], [libelle], [famille], [mode], [valeur], [description], [actif]) VALUES (411, N'livraison', N'Livraison et installation', N'option', N'fixe', CAST(290.00 AS Decimal(10, 2)), N'Livraison à domicile, déballage, cartographie du logement et mise en service.', 1)
INSERT [dbo].[Tarif] ([idTarif], [code], [libelle], [famille], [mode], [valeur], [description], [actif]) VALUES (412, N'maintenance', N'Contrat de maintenance (1 an)', N'option', N'fixe', CAST(490.00 AS Decimal(10, 2)), N'Deux visites d''entretien et les mises à jour logicielles prioritaires.', 1)
INSERT [dbo].[Tarif] ([idTarif], [code], [libelle], [famille], [mode], [valeur], [description], [actif]) VALUES (413, N'formation', N'Prise en main (2 h)', N'option', N'fixe', CAST(150.00 AS Decimal(10, 2)), N'Un technicien vous apprend à programmer les routines de votre robot.', 1)
SET IDENTITY_INSERT [dbo].[Tarif] OFF
GO
INSERT [dbo].[TypeEvenement] ([code], [libelle]) VALUES (N'atelier', N'Atelier')
INSERT [dbo].[TypeEvenement] ([code], [libelle]) VALUES (N'demo', N'Démonstration')
INSERT [dbo].[TypeEvenement] ([code], [libelle]) VALUES (N'lancement', N'Lancement')
INSERT [dbo].[TypeEvenement] ([code], [libelle]) VALUES (N'salon', N'Salon')
GO
SET IDENTITY_INSERT [dbo].[Utilisateur] ON 

INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (999, N'Dubois', N'Léa', N'redacteur@robotix.test', N'$2y$10$DqlkvHYpaT0q9./vCxtXfem4Y/M8PQKyf2tArhltH7guq5ZGpop/i', CAST(N'2026-10-05T17:34:53.643' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1000, N'Bernard', N'Hugo', N'admin@robotix.test', N'$2y$10$7SEbhZcldu4RE2M2M1Qk0O4jTNL5giy6XkdiLI63kn0viehnel6CG', CAST(N'2026-10-05T17:34:53.727' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1001, N'Martin', N'Camille', N'client@robotix.test', N'$2y$10$YssphpZ1m.Qmi2VxOhuc7uqCp0NNS66HhZPYvKop9agn2sWed4Qbm', CAST(N'2025-01-15T10:00:00.000' AS DateTime), 1, N'camille')
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1002, N'Bernard', N'Lucas', N'lucas.bernard@exemple.fr', N'$2y$10$fGGb7Apw/vxT0HKnlLo5H.bo/sWgdLVEIBXg4ss9lBnvHNVxKZGKu', CAST(N'2026-02-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1003, N'Petit', N'Emma', N'emma.petit@exemple.fr', N'$2y$10$3vu2lpTymD6ne/Y6jgkgp.I8yUfR4u3gbb9vl2E9FEzEXFNNr4jsy', CAST(N'2025-03-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1004, N'Robert', N'Nathan', N'nathan.robert@exemple.fr', N'$2y$10$9hMDnSixl2NPlJT1keeUae.UD41.41tVy1WOmj2pjeg7nj.pPwEJy', CAST(N'2025-04-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1005, N'Richard', N'Chloé', N'chloe.richard@exemple.fr', N'$2y$10$2VDbkhSParYuQqPkdpgbQuxwdJuolQLRUp0.p1a0mYiDAiTJwOIDG', CAST(N'2026-05-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1006, N'Durand', N'Louis', N'louis.durand@exemple.fr', N'$2y$10$HnPzMgRm1DPRQHhru1SdC.tFYnZ63HdxSNmv5l88vgjz2N/uR9WEi', CAST(N'2025-06-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1007, N'Leroy', N'Manon', N'manon.leroy@exemple.fr', N'$2y$10$XD0xVxNNgUqtpWrD8cJbRuIEGchLANzQs1qEMg6MgCR0NeaeCuCxq', CAST(N'2026-07-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1008, N'Moreau', N'Jules', N'jules.moreau@exemple.fr', N'$2y$10$IqaS/YzHfDrc0TyC7mA93OzwRFSTNBOU2asuyP1x7SfI93NXAPsLC', CAST(N'2025-08-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1009, N'Simon', N'Inès', N'ines.simon@exemple.fr', N'$2y$10$hBX1C60xz.RH.9CGB0mo/.lIvy7zZtbz7DxFWG0Nf.JdbFzImMdaS', CAST(N'2026-01-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1010, N'Laurent', N'Paul', N'paul.laurent@exemple.fr', N'$2y$10$oiwBIzHgCS2sBzM6P27cMeBImtML8uHng5RAxe8V10bBBgTEc1o.O', CAST(N'2025-02-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1011, N'Lefebvre', N'Sarah', N'sarah.lefebvre@exemple.fr', N'$2y$10$0DO0tXkgia4sWQxtsld7Q.yNZPLfdYAGGEgor2.pIckUocjiyTABy', CAST(N'2026-03-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1012, N'Michel', N'Hélène', N'helene.michel@exemple.fr', N'$2y$10$EmwIzvSEu24ER9.VomZ3.uVjA9nHO.8sWnQ/fIBiyCwJq0a1FjtfO', CAST(N'2025-04-15T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1013, N'Haddad', N'Karim', N'karim.haddad@robotix.test', N'$2y$10$LPrLjMYllCP/pzuH62jGxejS8ORXxcIwKZrQyFGNMkn7pi2rXB.ry', CAST(N'2025-09-01T09:00:00.000' AS DateTime), 1, N'karim.h')
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1014, N'Fontaine', N'Inès', N'ines.fontaine@robotix.test', N'$2y$10$.WtpWFTUMaAB2todxGcBJOD4Boy3tNfXllTRYhkNdnyFDGkR/5Rqq', CAST(N'2025-09-01T09:00:00.000' AS DateTime), 1, N'ines.f')
INSERT [dbo].[Utilisateur] ([idUtilisateur], [nom], [prenom], [email], [motDePasse], [dateInscription], [actif], [pseudo]) VALUES (1015, N'Garnier', N'Théo', N'theo.garnier@robotix.test', N'$2y$10$KHn3P.E3DDGfYWwMF5rdSu9p7gjj7oRra1b9SaNLm7b6ozpwaUnme', CAST(N'2025-09-01T09:00:00.000' AS DateTime), 1, N'theo.g')
SET IDENTITY_INSERT [dbo].[Utilisateur] OFF
GO
INSERT [dbo].[Administrateur] ([idUtilisateur], [dateNomination]) VALUES (1000, CAST(N'2026-10-05' AS Date))
GO
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1001, N'06 12 34 56 78', CAST(N'1994-03-12' AS Date), NULL, 176)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1002, N'06 21 43 65 87', CAST(N'2004-06-02' AS Date), NULL, 175)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1003, N'06 32 54 76 98', CAST(N'2005-11-20' AS Date), NULL, 175)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1004, N'07 11 22 33 44', CAST(N'1990-01-15' AS Date), NULL, 176)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1005, N'07 22 33 44 55', CAST(N'1987-09-09' AS Date), NULL, 176)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1006, N'04 72 10 20 30', CAST(N'1958-04-30' AS Date), NULL, 177)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1007, N'06 44 55 66 77', CAST(N'2003-02-14' AS Date), NULL, 175)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1008, N'06 55 66 77 88', CAST(N'1975-12-01' AS Date), NULL, 176)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1009, N'04 91 20 30 40', CAST(N'1962-07-07' AS Date), NULL, 177)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1010, N'01 42 30 40 50', CAST(N'1950-10-10' AS Date), NULL, 177)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1011, N'06 66 77 88 99', CAST(N'1999-08-25' AS Date), NULL, 176)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1012, N'06 77 88 99 00', CAST(N'1963-05-05' AS Date), NULL, 177)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1013, N'06 10 20 30 40', CAST(N'1985-02-11' AS Date), NULL, 176)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1014, N'06 20 30 40 50', CAST(N'1990-06-21' AS Date), NULL, 176)
INSERT [dbo].[Client] ([idUtilisateur], [telephone], [dateNaissance], [photo], [idCategorieAge]) VALUES (1015, N'06 30 40 50 60', CAST(N'1992-12-03' AS Date), NULL, 176)
GO
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1001, 393)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1001, 394)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1002, 395)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1003, 393)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1003, 395)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1004, 396)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1005, 394)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1006, 393)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1007, 395)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1008, 394)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1008, 396)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1009, 393)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1010, 394)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1011, 395)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1012, 393)
INSERT [dbo].[ClientInteret] ([idUtilisateur], [idCategorie]) VALUES (1012, 394)
GO
INSERT [dbo].[CompatibiliteProduit] ([idProduit1], [idProduit2]) VALUES (981, 983)
INSERT [dbo].[CompatibiliteProduit] ([idProduit1], [idProduit2]) VALUES (984, 985)
INSERT [dbo].[CompatibiliteProduit] ([idProduit1], [idProduit2]) VALUES (987, 988)
INSERT [dbo].[CompatibiliteProduit] ([idProduit1], [idProduit2]) VALUES (989, 990)
GO
SET IDENTITY_INSERT [dbo].[Image] ON 

INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1961, N'rbx-uni-g1-1.svg', N'Unitree G1 — Vue de face', 1, 981)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1962, N'rbx-uni-g1-2.svg', N'Unitree G1 — En action', 2, 981)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1963, N'rbx-uni-h1-1.svg', N'Unitree H1 — Vue de face', 1, 982)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1964, N'rbx-uni-h1-2.svg', N'Unitree H1 — En action', 2, 982)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1965, N'rbx-uni-r1-1.svg', N'Unitree R1 — Vue de face', 1, 983)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1966, N'rbx-uni-r1-2.svg', N'Unitree R1 — En action', 2, 983)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1967, N'rbx-fig-02-1.svg', N'Figure 02 — Vue de face', 1, 984)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1968, N'rbx-fig-02-2.svg', N'Figure 02 — En action', 2, 984)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1969, N'rbx-fig-03-1.svg', N'Figure 03 — Vue de face', 1, 985)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1970, N'rbx-fig-03-2.svg', N'Figure 03 — En action', 2, 985)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1971, N'rbx-agi-dgt-1.svg', N'Digit — Vue de face', 1, 986)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1972, N'rbx-agi-dgt-2.svg', N'Digit — En action', 2, 986)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1973, N'rbx-1x-neo-1.svg', N'NEO Gamma — Vue de face', 1, 987)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1974, N'rbx-1x-neo-2.svg', N'NEO Gamma — En action', 2, 987)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1975, N'rbx-1x-eve-1.svg', N'EVE — Vue de face', 1, 988)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1976, N'rbx-1x-eve-2.svg', N'EVE — En action', 2, 988)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1977, N'rbx-pol-r2-1.svg', N'Reachy 2 — Vue de face', 1, 989)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1978, N'rbx-pol-r2-2.svg', N'Reachy 2 — En action', 2, 989)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1979, N'rbx-pol-mini-1.svg', N'Reachy Mini — Vue de face', 1, 990)
INSERT [dbo].[Image] ([idImage], [fichier], [legende], [numOrdre], [idProduit]) VALUES (1980, N'rbx-pol-mini-2.svg', N'Reachy Mini — En action', 2, 990)
SET IDENTITY_INSERT [dbo].[Image] OFF
GO
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1001, N'intermédiaire', 3)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1002, N'confirmé', 3)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1003, N'débutant', 2)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1004, N'confirmé', 2)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1005, N'débutant', 1)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1006, N'débutant', 1)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1007, N'intermédiaire', 2)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1011, N'intermédiaire', 2)
INSERT [dbo].[Membre] ([idUtilisateur], [niveau], [nbEvenements]) VALUES (1012, N'débutant', 1)
GO
INSERT [dbo].[Redacteur] ([idUtilisateur], [pseudo], [biographie]) VALUES (999, N'LeaTech', N'Journaliste tech passionnée par la robotique humanoïde.')
GO
SET IDENTITY_INSERT [dbo].[Reunion] ON 

INSERT [dbo].[Reunion] ([idReunion], [dateReunion], [objet], [idShowroom]) VALUES (19, CAST(N'2026-09-30T18:00:00.000' AS DateTime), N'Bilan des ateliers de septembre', 296)
INSERT [dbo].[Reunion] ([idReunion], [dateReunion], [objet], [idShowroom]) VALUES (20, CAST(N'2026-11-03T18:00:00.000' AS DateTime), N'Préparation des ateliers de Noël', 295)
SET IDENTITY_INSERT [dbo].[Reunion] OFF
GO
SET IDENTITY_INSERT [dbo].[Adhesion] ON 

INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1021, 1001, 2025, CAST(N'2025-01-15T10:00:00.000' AS DateTime), 408, CAST(99.00 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1022, 1001, 2026, CAST(N'2026-01-15T10:00:00.000' AS DateTime), 408, CAST(99.00 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1023, 1002, 2026, CAST(N'2026-02-15T10:00:00.000' AS DateTime), 407, CAST(41.65 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1024, 1003, 2025, CAST(N'2025-03-15T10:00:00.000' AS DateTime), 407, CAST(41.65 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1025, 1003, 2026, CAST(N'2026-03-15T10:00:00.000' AS DateTime), 408, CAST(84.15 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1026, 1004, 2025, CAST(N'2025-04-15T10:00:00.000' AS DateTime), 409, CAST(199.00 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1027, 1004, 2026, CAST(N'2026-04-15T10:00:00.000' AS DateTime), 409, CAST(199.00 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1028, 1005, 2026, CAST(N'2026-05-15T10:00:00.000' AS DateTime), 408, CAST(99.00 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1029, 1006, 2025, CAST(N'2025-06-15T10:00:00.000' AS DateTime), 408, CAST(89.10 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1030, 1006, 2026, CAST(N'2026-06-15T10:00:00.000' AS DateTime), 408, CAST(89.10 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1031, 1007, 2026, CAST(N'2026-07-15T10:00:00.000' AS DateTime), 407, CAST(41.65 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1032, 1008, 2025, CAST(N'2025-08-15T10:00:00.000' AS DateTime), 409, CAST(199.00 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1033, 1009, 2026, CAST(N'2026-01-15T10:00:00.000' AS DateTime), 407, CAST(44.10 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1034, 1010, 2025, CAST(N'2025-02-15T10:00:00.000' AS DateTime), 407, CAST(44.10 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1035, 1011, 2026, CAST(N'2026-03-15T10:00:00.000' AS DateTime), 409, CAST(199.00 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1036, 1012, 2025, CAST(N'2025-04-15T10:00:00.000' AS DateTime), 408, CAST(89.10 AS Decimal(10, 2)))
INSERT [dbo].[Adhesion] ([idAdhesion], [idUtilisateur], [annee], [dateAdhesion], [idTarif], [montant]) VALUES (1037, 1012, 2026, CAST(N'2026-04-15T10:00:00.000' AS DateTime), 408, CAST(89.10 AS Decimal(10, 2)))
SET IDENTITY_INSERT [dbo].[Adhesion] OFF
GO
SET IDENTITY_INSERT [dbo].[Adresse] ON 

INSERT [dbo].[Adresse] ([idAdresse], [libelle], [ligne1], [ligne2], [codePostal], [ville], [pays], [idUtilisateur]) VALUES (138, N'Domicile', N'10 rue de la République', NULL, N'69002', N'Lyon', N'France', 1001)
SET IDENTITY_INSERT [dbo].[Adresse] OFF
GO
INSERT [dbo].[Animateur] ([idUtilisateur], [specialite], [idRemplacant]) VALUES (1013, N'Programmation des robots', 1014)
INSERT [dbo].[Animateur] ([idUtilisateur], [specialite], [idRemplacant]) VALUES (1014, N'Robotique domestique', 1015)
INSERT [dbo].[Animateur] ([idUtilisateur], [specialite], [idRemplacant]) VALUES (1015, N'Robots éducatifs', 1013)
GO
INSERT [dbo].[Convocation] ([idReunion], [idAnimateur], [dateEnvoi]) VALUES (19, 1013, CAST(N'2026-09-20T09:00:00.000' AS DateTime))
INSERT [dbo].[Convocation] ([idReunion], [idAnimateur], [dateEnvoi]) VALUES (19, 1014, CAST(N'2026-09-20T09:00:00.000' AS DateTime))
INSERT [dbo].[Convocation] ([idReunion], [idAnimateur], [dateEnvoi]) VALUES (19, 1015, CAST(N'2026-09-20T09:00:00.000' AS DateTime))
INSERT [dbo].[Convocation] ([idReunion], [idAnimateur], [dateEnvoi]) VALUES (20, 1013, CAST(N'2026-09-20T09:00:00.000' AS DateTime))
INSERT [dbo].[Convocation] ([idReunion], [idAnimateur], [dateEnvoi]) VALUES (20, 1015, CAST(N'2026-09-20T09:00:00.000' AS DateTime))
GO
SET IDENTITY_INSERT [dbo].[Evenement] ON 

INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1234, N'Démonstration Unitree G1', N'Venez voir le G1 marcher, saluer et jouer au ballon.', N'demo', CAST(N'2026-10-08T14:00:00.000' AS DateTime), CAST(N'2026-10-08T17:00:00.000' AS DateTime), 295, 981, 30, 1014)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1235, N'Lancement de Figure 03 en France', N'Présentation officielle et premières précommandes.', N'lancement', CAST(N'2026-10-15T10:00:00.000' AS DateTime), CAST(N'2026-10-15T18:00:00.000' AS DateTime), 296, 985, 50, 1015)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1236, N'Atelier programmation Reachy Mini', N'Initiation à la programmation Python, dès 10 ans (12 places).', N'atelier', CAST(N'2026-10-21T18:30:00.000' AS DateTime), CAST(N'2026-10-21T20:30:00.000' AS DateTime), 297, 990, 12, 1013)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1237, N'Salon Innorobo — Lyon Eurexpo', N'Retrouvez Robotix sur le stand B12 pendant deux jours.', N'salon', CAST(N'2026-10-24T09:00:00.000' AS DateTime), CAST(N'2026-10-25T19:00:00.000' AS DateTime), NULL, NULL, 200, 1015)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1238, N'Démonstration NEO Gamma', N'NEO Gamma en situation dans un salon reconstitué.', N'demo', CAST(N'2026-11-05T14:00:00.000' AS DateTime), CAST(N'2026-11-05T17:00:00.000' AS DateTime), 296, 987, 30, 1014)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1239, N'Atelier : apprendre un geste à Reachy 2', N'Apprentissage par démonstration avec les bras de Reachy 2.', N'atelier', CAST(N'2026-11-12T18:30:00.000' AS DateTime), CAST(N'2026-11-12T20:30:00.000' AS DateTime), 295, 989, 12, 1013)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1240, N'Journée Unitree H1', N'Essais et rencontre avec un ingénieur Unitree.', N'lancement', CAST(N'2026-11-19T10:00:00.000' AS DateTime), CAST(N'2026-11-19T18:00:00.000' AS DateTime), 295, 982, 50, 1015)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1241, N'Démonstration Digit', N'Digit porte vos courses : démonstration de charge.', N'demo', CAST(N'2026-11-25T14:00:00.000' AS DateTime), CAST(N'2026-11-25T17:00:00.000' AS DateTime), 297, 986, 30, 1014)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1242, N'Démonstration EVE', N'Découvrez le robot de présence EVE.', N'demo', CAST(N'2026-12-03T14:00:00.000' AS DateTime), CAST(N'2026-12-03T17:00:00.000' AS DateTime), 295, 988, 30, 1014)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1243, N'Atelier famille Unitree R1', N'Programmer une danse de Noël en famille.', N'atelier', CAST(N'2026-12-09T18:30:00.000' AS DateTime), CAST(N'2026-12-09T20:30:00.000' AS DateTime), 296, 983, 12, 1013)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1244, N'Marché de Noël des robots', N'Toute la gamme exposée, offres spéciales.', N'salon', CAST(N'2026-12-12T10:00:00.000' AS DateTime), CAST(N'2026-12-12T18:00:00.000' AS DateTime), 297, NULL, 200, 1015)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1245, N'Démonstration Figure 02', N'Figure 02 range une cuisine en direct.', N'demo', CAST(N'2026-12-17T14:00:00.000' AS DateTime), CAST(N'2026-12-17T17:00:00.000' AS DateTime), 296, 984, 30, 1014)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1246, N'Atelier découverte Reachy Mini', N'Événement de rentrée du Club Robotix.', N'atelier', CAST(N'2026-09-09T18:30:00.000' AS DateTime), CAST(N'2026-09-09T20:30:00.000' AS DateTime), 297, 990, 12, 1013)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1247, N'Démonstration de rentrée Figure 02', N'Événement de rentrée du Club Robotix.', N'demo', CAST(N'2026-09-16T14:00:00.000' AS DateTime), CAST(N'2026-09-16T17:00:00.000' AS DateTime), 295, 984, 30, 1014)
INSERT [dbo].[Evenement] ([idEvenement], [titre], [description], [type], [dateDebut], [dateFin], [idShowroom], [idProduit], [nbPlaces], [idAnimateur]) VALUES (1248, N'Atelier programmation Unitree R1', N'Événement de rentrée du Club Robotix.', N'atelier', CAST(N'2026-09-23T18:00:00.000' AS DateTime), CAST(N'2026-09-23T21:00:00.000' AS DateTime), 296, 983, 12, 1015)
SET IDENTITY_INSERT [dbo].[Evenement] OFF
GO
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1001, 1236, CAST(N'2026-09-01T10:00:00.000' AS DateTime), NULL, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1001, 1246, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, N'A programmé un salut de la tête en Python.')
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1001, 1247, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, N'Questions sur la mise en service à domicile.')
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1002, 1243, CAST(N'2026-09-01T10:00:00.000' AS DateTime), NULL, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1002, 1246, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, N'A créé une animation des antennes.')
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1002, 1248, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, N'Chorégraphie de huit pas.')
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1003, 1246, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 0, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1003, 1248, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, N'Programme d''équilibre sur une jambe.')
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1004, 1239, CAST(N'2026-09-01T10:00:00.000' AS DateTime), NULL, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1004, 1248, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, N'Script de marche en carré.')
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1005, 1243, CAST(N'2026-09-01T10:00:00.000' AS DateTime), NULL, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1006, 1247, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1007, 1236, CAST(N'2026-09-01T10:00:00.000' AS DateTime), NULL, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1007, 1246, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 1, N'A fait suivre un visage à Reachy Mini.')
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1011, 1239, CAST(N'2026-09-01T10:00:00.000' AS DateTime), NULL, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1011, 1248, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 0, NULL)
INSERT [dbo].[Inscription] ([idMembre], [idEvenement], [dateInscription], [present], [travailRealise]) VALUES (1012, 1247, CAST(N'2026-09-01T10:00:00.000' AS DateTime), 0, NULL)
GO
SET IDENTITY_INSERT [dbo].[Panier] ON 

INSERT [dbo].[Panier] ([idPanier], [idEvenement], [idUtilisateur], [nomEvenement], [dateResa], [nbPlace]) VALUES (141, 1236, 1001, N'Atelier programmation Reachy Mini', CAST(N'2026-10-01T09:00:00.000' AS DateTime), 2)
INSERT [dbo].[Panier] ([idPanier], [idEvenement], [idUtilisateur], [nomEvenement], [dateResa], [nbPlace]) VALUES (142, 1234, 1003, N'Démonstration Unitree G1', CAST(N'2026-10-01T09:00:00.000' AS DateTime), 3)
SET IDENTITY_INSERT [dbo].[Panier] OFF
GO
INSERT [dbo].[PointOrdreJour] ([idReunion], [numOrdre], [libelle]) VALUES (19, 1, N'Bilan de fréquentation des ateliers')
INSERT [dbo].[PointOrdreJour] ([idReunion], [numOrdre], [libelle]) VALUES (19, 2, N'Retours des membres sur Reachy Mini')
INSERT [dbo].[PointOrdreJour] ([idReunion], [numOrdre], [libelle]) VALUES (19, 3, N'Planning des démonstrations d''octobre')
INSERT [dbo].[PointOrdreJour] ([idReunion], [numOrdre], [libelle]) VALUES (20, 1, N'Programme de l''atelier famille Unitree R1')
INSERT [dbo].[PointOrdreJour] ([idReunion], [numOrdre], [libelle]) VALUES (20, 2, N'Matériel à commander')
INSERT [dbo].[PointOrdreJour] ([idReunion], [numOrdre], [libelle]) VALUES (20, 3, N'Répartition des remplacements')
INSERT [dbo].[PointOrdreJour] ([idReunion], [numOrdre], [libelle]) VALUES (20, 4, N'Questions diverses')
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UQ_CategorieAge_libelle]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[CategorieAge] ADD  CONSTRAINT [UQ_CategorieAge_libelle] UNIQUE NONCLUSTERED 
(
	[libelle] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UQ_Produit_reference]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Produit] ADD  CONSTRAINT [UQ_Produit_reference] UNIQUE NONCLUSTERED 
(
	[reference] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [UQ_Reduction_categorie]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Reduction] ADD  CONSTRAINT [UQ_Reduction_categorie] UNIQUE NONCLUSTERED 
(
	[idCategorieAge] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UQ_Tarif_code]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Tarif] ADD  CONSTRAINT [UQ_Tarif_code] UNIQUE NONCLUSTERED 
(
	[code] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UQ_Utilisateur_email]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Utilisateur] ADD  CONSTRAINT [UQ_Utilisateur_email] UNIQUE NONCLUSTERED 
(
	[email] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UX_Utilisateur_pseudo]    Script Date: 06/10/2026 17:43:02 ******/
CREATE UNIQUE NONCLUSTERED INDEX [UX_Utilisateur_pseudo] ON [dbo].[Utilisateur]
(
	[pseudo] ASC
)
WHERE ([pseudo] IS NOT NULL)
WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [UQ_Image_ordre]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Image] ADD  CONSTRAINT [UQ_Image_ordre] UNIQUE NONCLUSTERED 
(
	[idProduit] ASC,
	[numOrdre] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UQ_Redacteur_pseudo]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Redacteur] ADD  CONSTRAINT [UQ_Redacteur_pseudo] UNIQUE NONCLUSTERED 
(
	[pseudo] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [UQ_Adhesion_annee]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Adhesion] ADD  CONSTRAINT [UQ_Adhesion_annee] UNIQUE NONCLUSTERED 
(
	[idUtilisateur] ASC,
	[annee] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_PADDING ON
GO
/****** Object:  Index [UQ_Article_slug]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Article] ADD  CONSTRAINT [UQ_Article_slug] UNIQUE NONCLUSTERED 
(
	[slug] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
/****** Object:  Index [UQ_Paiement_idCommande]    Script Date: 06/10/2026 17:43:02 ******/
ALTER TABLE [dbo].[Paiement] ADD  CONSTRAINT [UQ_Paiement_idCommande] UNIQUE NONCLUSTERED 
(
	[idCommande] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, IGNORE_DUP_KEY = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
GO
ALTER TABLE [dbo].[HistoriqueAdhesion] ADD  CONSTRAINT [DF_Historique_date]  DEFAULT (getdate()) FOR [dateHistorisation]
GO
ALTER TABLE [dbo].[MailAEnvoyer] ADD  CONSTRAINT [DF_Mail_date]  DEFAULT (getdate()) FOR [dateCreation]
GO
ALTER TABLE [dbo].[MailAEnvoyer] ADD  CONSTRAINT [DF_Mail_envoye]  DEFAULT ((0)) FOR [envoye]
GO
ALTER TABLE [dbo].[Produit] ADD  CONSTRAINT [DF_Produit_stock]  DEFAULT ((0)) FOR [stock]
GO
ALTER TABLE [dbo].[Produit] ADD  CONSTRAINT [DF_Produit_actif]  DEFAULT ((1)) FOR [actif]
GO
ALTER TABLE [dbo].[Produit] ADD  CONSTRAINT [DF_Produit_dateAjout]  DEFAULT (CONVERT([date],getdate())) FOR [dateAjout]
GO
ALTER TABLE [dbo].[Tarif] ADD  CONSTRAINT [DF_Tarif_actif]  DEFAULT ((1)) FOR [actif]
GO
ALTER TABLE [dbo].[Utilisateur] ADD  CONSTRAINT [DF_Utilisateur_dateInscription]  DEFAULT (getdate()) FOR [dateInscription]
GO
ALTER TABLE [dbo].[Utilisateur] ADD  CONSTRAINT [DF_Utilisateur_actif]  DEFAULT ((1)) FOR [actif]
GO
ALTER TABLE [dbo].[Journal] ADD  CONSTRAINT [DF_Journal_dateAction]  DEFAULT (getdate()) FOR [dateAction]
GO
ALTER TABLE [dbo].[Membre] ADD  CONSTRAINT [DF_Membre_niveau]  DEFAULT ('débutant') FOR [niveau]
GO
ALTER TABLE [dbo].[Membre] ADD  CONSTRAINT [DF_Membre_nb]  DEFAULT ((0)) FOR [nbEvenements]
GO
ALTER TABLE [dbo].[MessageContact] ADD  CONSTRAINT [DF_MessageContact_date]  DEFAULT (getdate()) FOR [dateEnvoi]
GO
ALTER TABLE [dbo].[MessageContact] ADD  CONSTRAINT [DF_MessageContact_traite]  DEFAULT ((0)) FOR [traite]
GO
ALTER TABLE [dbo].[Suivre] ADD  CONSTRAINT [DF_Suivre_dateSuivi]  DEFAULT (CONVERT([date],getdate())) FOR [dateSuivi]
GO
ALTER TABLE [dbo].[Adhesion] ADD  CONSTRAINT [DF_Adhesion_date]  DEFAULT (getdate()) FOR [dateAdhesion]
GO
ALTER TABLE [dbo].[Article] ADD  CONSTRAINT [DF_Article_estPublie]  DEFAULT ((0)) FOR [estPublie]
GO
ALTER TABLE [dbo].[Commande] ADD  CONSTRAINT [DF_Commande_dateCommande]  DEFAULT (getdate()) FOR [dateCommande]
GO
ALTER TABLE [dbo].[Convocation] ADD  CONSTRAINT [DF_Convocation_date]  DEFAULT (getdate()) FOR [dateEnvoi]
GO
ALTER TABLE [dbo].[Evenement] ADD  CONSTRAINT [DF_Evenement_nbPlaces]  DEFAULT ((30)) FOR [nbPlaces]
GO
ALTER TABLE [dbo].[HistoriqueStatut] ADD  CONSTRAINT [DF_HistoriqueStatut_dateAction]  DEFAULT (getdate()) FOR [dateAction]
GO
ALTER TABLE [dbo].[Inscription] ADD  CONSTRAINT [DF_Inscription_date]  DEFAULT (getdate()) FOR [dateInscription]
GO
ALTER TABLE [dbo].[Paiement] ADD  CONSTRAINT [DF_Paiement_datePaiement]  DEFAULT (getdate()) FOR [datePaiement]
GO
ALTER TABLE [dbo].[Panier] ADD  CONSTRAINT [DF_Panier_date]  DEFAULT (getdate()) FOR [dateResa]
GO
ALTER TABLE [dbo].[Produit]  WITH CHECK ADD  CONSTRAINT [FK_Produit_Categorie] FOREIGN KEY([idCategorie])
REFERENCES [dbo].[Categorie] ([idCategorie])
GO
ALTER TABLE [dbo].[Produit] CHECK CONSTRAINT [FK_Produit_Categorie]
GO
ALTER TABLE [dbo].[Produit]  WITH CHECK ADD  CONSTRAINT [FK_Produit_Marque] FOREIGN KEY([idMarque])
REFERENCES [dbo].[Marque] ([idMarque])
GO
ALTER TABLE [dbo].[Produit] CHECK CONSTRAINT [FK_Produit_Marque]
GO
ALTER TABLE [dbo].[Reduction]  WITH CHECK ADD  CONSTRAINT [FK_Reduction_CategorieAge] FOREIGN KEY([idCategorieAge])
REFERENCES [dbo].[CategorieAge] ([idCategorieAge])
GO
ALTER TABLE [dbo].[Reduction] CHECK CONSTRAINT [FK_Reduction_CategorieAge]
GO
ALTER TABLE [dbo].[Administrateur]  WITH CHECK ADD  CONSTRAINT [FK_Administrateur_Utilisateur] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Utilisateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Administrateur] CHECK CONSTRAINT [FK_Administrateur_Utilisateur]
GO
ALTER TABLE [dbo].[Client]  WITH CHECK ADD  CONSTRAINT [FK_Client_CategorieAge] FOREIGN KEY([idCategorieAge])
REFERENCES [dbo].[CategorieAge] ([idCategorieAge])
GO
ALTER TABLE [dbo].[Client] CHECK CONSTRAINT [FK_Client_CategorieAge]
GO
ALTER TABLE [dbo].[Client]  WITH CHECK ADD  CONSTRAINT [FK_Client_Utilisateur] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Utilisateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Client] CHECK CONSTRAINT [FK_Client_Utilisateur]
GO
ALTER TABLE [dbo].[ClientInteret]  WITH CHECK ADD  CONSTRAINT [FK_ClientInteret_Categorie] FOREIGN KEY([idCategorie])
REFERENCES [dbo].[Categorie] ([idCategorie])
GO
ALTER TABLE [dbo].[ClientInteret] CHECK CONSTRAINT [FK_ClientInteret_Categorie]
GO
ALTER TABLE [dbo].[ClientInteret]  WITH CHECK ADD  CONSTRAINT [FK_ClientInteret_Client] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Client] ([idUtilisateur])
GO
ALTER TABLE [dbo].[ClientInteret] CHECK CONSTRAINT [FK_ClientInteret_Client]
GO
ALTER TABLE [dbo].[CompatibiliteProduit]  WITH CHECK ADD  CONSTRAINT [FK_Compatibilite_Produit1] FOREIGN KEY([idProduit1])
REFERENCES [dbo].[Produit] ([idProduit])
GO
ALTER TABLE [dbo].[CompatibiliteProduit] CHECK CONSTRAINT [FK_Compatibilite_Produit1]
GO
ALTER TABLE [dbo].[CompatibiliteProduit]  WITH CHECK ADD  CONSTRAINT [FK_Compatibilite_Produit2] FOREIGN KEY([idProduit2])
REFERENCES [dbo].[Produit] ([idProduit])
GO
ALTER TABLE [dbo].[CompatibiliteProduit] CHECK CONSTRAINT [FK_Compatibilite_Produit2]
GO
ALTER TABLE [dbo].[Image]  WITH CHECK ADD  CONSTRAINT [FK_Image_Produit] FOREIGN KEY([idProduit])
REFERENCES [dbo].[Produit] ([idProduit])
GO
ALTER TABLE [dbo].[Image] CHECK CONSTRAINT [FK_Image_Produit]
GO
ALTER TABLE [dbo].[Journal]  WITH CHECK ADD  CONSTRAINT [FK_Journal_Utilisateur] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Utilisateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Journal] CHECK CONSTRAINT [FK_Journal_Utilisateur]
GO
ALTER TABLE [dbo].[Membre]  WITH CHECK ADD  CONSTRAINT [FK_Membre_Client] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Client] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Membre] CHECK CONSTRAINT [FK_Membre_Client]
GO
ALTER TABLE [dbo].[MessageContact]  WITH CHECK ADD  CONSTRAINT [FK_MessageContact_Produit] FOREIGN KEY([idProduit])
REFERENCES [dbo].[Produit] ([idProduit])
GO
ALTER TABLE [dbo].[MessageContact] CHECK CONSTRAINT [FK_MessageContact_Produit]
GO
ALTER TABLE [dbo].[Redacteur]  WITH CHECK ADD  CONSTRAINT [FK_Redacteur_Utilisateur] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Utilisateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Redacteur] CHECK CONSTRAINT [FK_Redacteur_Utilisateur]
GO
ALTER TABLE [dbo].[Reunion]  WITH CHECK ADD  CONSTRAINT [FK_Reunion_Showroom] FOREIGN KEY([idShowroom])
REFERENCES [dbo].[Showroom] ([idShowroom])
GO
ALTER TABLE [dbo].[Reunion] CHECK CONSTRAINT [FK_Reunion_Showroom]
GO
ALTER TABLE [dbo].[Suivre]  WITH CHECK ADD  CONSTRAINT [FK_Suivre_Marque] FOREIGN KEY([idMarque])
REFERENCES [dbo].[Marque] ([idMarque])
GO
ALTER TABLE [dbo].[Suivre] CHECK CONSTRAINT [FK_Suivre_Marque]
GO
ALTER TABLE [dbo].[Suivre]  WITH CHECK ADD  CONSTRAINT [FK_Suivre_Utilisateur] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Utilisateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Suivre] CHECK CONSTRAINT [FK_Suivre_Utilisateur]
GO
ALTER TABLE [dbo].[Adhesion]  WITH CHECK ADD  CONSTRAINT [FK_Adhesion_Client] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Client] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Adhesion] CHECK CONSTRAINT [FK_Adhesion_Client]
GO
ALTER TABLE [dbo].[Adhesion]  WITH CHECK ADD  CONSTRAINT [FK_Adhesion_Tarif] FOREIGN KEY([idTarif])
REFERENCES [dbo].[Tarif] ([idTarif])
GO
ALTER TABLE [dbo].[Adhesion] CHECK CONSTRAINT [FK_Adhesion_Tarif]
GO
ALTER TABLE [dbo].[Adresse]  WITH CHECK ADD  CONSTRAINT [FK_Adresse_Client] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Client] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Adresse] CHECK CONSTRAINT [FK_Adresse_Client]
GO
ALTER TABLE [dbo].[Animateur]  WITH CHECK ADD  CONSTRAINT [FK_Animateur_Client] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Client] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Animateur] CHECK CONSTRAINT [FK_Animateur_Client]
GO
ALTER TABLE [dbo].[Animateur]  WITH CHECK ADD  CONSTRAINT [FK_Animateur_Remplacant] FOREIGN KEY([idRemplacant])
REFERENCES [dbo].[Animateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Animateur] CHECK CONSTRAINT [FK_Animateur_Remplacant]
GO
ALTER TABLE [dbo].[Article]  WITH CHECK ADD  CONSTRAINT [FK_Article_Marque] FOREIGN KEY([idMarque])
REFERENCES [dbo].[Marque] ([idMarque])
GO
ALTER TABLE [dbo].[Article] CHECK CONSTRAINT [FK_Article_Marque]
GO
ALTER TABLE [dbo].[Article]  WITH CHECK ADD  CONSTRAINT [FK_Article_Redacteur] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Redacteur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Article] CHECK CONSTRAINT [FK_Article_Redacteur]
GO
ALTER TABLE [dbo].[Commande]  WITH CHECK ADD  CONSTRAINT [FK_Commande_AdresseFacturation] FOREIGN KEY([idAdresseFacturation])
REFERENCES [dbo].[Adresse] ([idAdresse])
GO
ALTER TABLE [dbo].[Commande] CHECK CONSTRAINT [FK_Commande_AdresseFacturation]
GO
ALTER TABLE [dbo].[Commande]  WITH CHECK ADD  CONSTRAINT [FK_Commande_AdresseLivraison] FOREIGN KEY([idAdresseLivraison])
REFERENCES [dbo].[Adresse] ([idAdresse])
GO
ALTER TABLE [dbo].[Commande] CHECK CONSTRAINT [FK_Commande_AdresseLivraison]
GO
ALTER TABLE [dbo].[Commande]  WITH CHECK ADD  CONSTRAINT [FK_Commande_Client] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Client] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Commande] CHECK CONSTRAINT [FK_Commande_Client]
GO
ALTER TABLE [dbo].[Contenir]  WITH CHECK ADD  CONSTRAINT [FK_Contenir_Commande] FOREIGN KEY([idCommande])
REFERENCES [dbo].[Commande] ([idCommande])
GO
ALTER TABLE [dbo].[Contenir] CHECK CONSTRAINT [FK_Contenir_Commande]
GO
ALTER TABLE [dbo].[Contenir]  WITH CHECK ADD  CONSTRAINT [FK_Contenir_Produit] FOREIGN KEY([idProduit])
REFERENCES [dbo].[Produit] ([idProduit])
GO
ALTER TABLE [dbo].[Contenir] CHECK CONSTRAINT [FK_Contenir_Produit]
GO
ALTER TABLE [dbo].[Convocation]  WITH CHECK ADD  CONSTRAINT [FK_Convocation_Animateur] FOREIGN KEY([idAnimateur])
REFERENCES [dbo].[Animateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Convocation] CHECK CONSTRAINT [FK_Convocation_Animateur]
GO
ALTER TABLE [dbo].[Convocation]  WITH CHECK ADD  CONSTRAINT [FK_Convocation_Reunion] FOREIGN KEY([idReunion])
REFERENCES [dbo].[Reunion] ([idReunion])
GO
ALTER TABLE [dbo].[Convocation] CHECK CONSTRAINT [FK_Convocation_Reunion]
GO
ALTER TABLE [dbo].[Evenement]  WITH CHECK ADD  CONSTRAINT [FK_Evenement_Animateur] FOREIGN KEY([idAnimateur])
REFERENCES [dbo].[Animateur] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Evenement] CHECK CONSTRAINT [FK_Evenement_Animateur]
GO
ALTER TABLE [dbo].[Evenement]  WITH CHECK ADD  CONSTRAINT [FK_Evenement_Produit] FOREIGN KEY([idProduit])
REFERENCES [dbo].[Produit] ([idProduit])
GO
ALTER TABLE [dbo].[Evenement] CHECK CONSTRAINT [FK_Evenement_Produit]
GO
ALTER TABLE [dbo].[Evenement]  WITH CHECK ADD  CONSTRAINT [FK_Evenement_Showroom] FOREIGN KEY([idShowroom])
REFERENCES [dbo].[Showroom] ([idShowroom])
GO
ALTER TABLE [dbo].[Evenement] CHECK CONSTRAINT [FK_Evenement_Showroom]
GO
ALTER TABLE [dbo].[Evenement]  WITH CHECK ADD  CONSTRAINT [FK_Evenement_Type] FOREIGN KEY([type])
REFERENCES [dbo].[TypeEvenement] ([code])
GO
ALTER TABLE [dbo].[Evenement] CHECK CONSTRAINT [FK_Evenement_Type]
GO
ALTER TABLE [dbo].[HistoriqueStatut]  WITH CHECK ADD  CONSTRAINT [FK_HistoriqueStatut_Commande] FOREIGN KEY([idCommande])
REFERENCES [dbo].[Commande] ([idCommande])
GO
ALTER TABLE [dbo].[HistoriqueStatut] CHECK CONSTRAINT [FK_HistoriqueStatut_Commande]
GO
ALTER TABLE [dbo].[Inscription]  WITH CHECK ADD  CONSTRAINT [FK_Inscription_Evenement] FOREIGN KEY([idEvenement])
REFERENCES [dbo].[Evenement] ([idEvenement])
GO
ALTER TABLE [dbo].[Inscription] CHECK CONSTRAINT [FK_Inscription_Evenement]
GO
ALTER TABLE [dbo].[Inscription]  WITH CHECK ADD  CONSTRAINT [FK_Inscription_Membre] FOREIGN KEY([idMembre])
REFERENCES [dbo].[Membre] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Inscription] CHECK CONSTRAINT [FK_Inscription_Membre]
GO
ALTER TABLE [dbo].[Mentionner]  WITH CHECK ADD  CONSTRAINT [FK_Mentionner_Article] FOREIGN KEY([idArticle])
REFERENCES [dbo].[Article] ([idArticle])
GO
ALTER TABLE [dbo].[Mentionner] CHECK CONSTRAINT [FK_Mentionner_Article]
GO
ALTER TABLE [dbo].[Mentionner]  WITH CHECK ADD  CONSTRAINT [FK_Mentionner_Produit] FOREIGN KEY([idProduit])
REFERENCES [dbo].[Produit] ([idProduit])
GO
ALTER TABLE [dbo].[Mentionner] CHECK CONSTRAINT [FK_Mentionner_Produit]
GO
ALTER TABLE [dbo].[Paiement]  WITH CHECK ADD  CONSTRAINT [FK_Paiement_Commande] FOREIGN KEY([idCommande])
REFERENCES [dbo].[Commande] ([idCommande])
GO
ALTER TABLE [dbo].[Paiement] CHECK CONSTRAINT [FK_Paiement_Commande]
GO
ALTER TABLE [dbo].[Panier]  WITH CHECK ADD  CONSTRAINT [FK_Panier_Client] FOREIGN KEY([idUtilisateur])
REFERENCES [dbo].[Client] ([idUtilisateur])
GO
ALTER TABLE [dbo].[Panier] CHECK CONSTRAINT [FK_Panier_Client]
GO
ALTER TABLE [dbo].[Panier]  WITH CHECK ADD  CONSTRAINT [FK_Panier_Evenement] FOREIGN KEY([idEvenement])
REFERENCES [dbo].[Evenement] ([idEvenement])
GO
ALTER TABLE [dbo].[Panier] CHECK CONSTRAINT [FK_Panier_Evenement]
GO
ALTER TABLE [dbo].[PointOrdreJour]  WITH CHECK ADD  CONSTRAINT [FK_Point_Reunion] FOREIGN KEY([idReunion])
REFERENCES [dbo].[Reunion] ([idReunion])
GO
ALTER TABLE [dbo].[PointOrdreJour] CHECK CONSTRAINT [FK_Point_Reunion]
GO
ALTER TABLE [dbo].[Aborder]  WITH CHECK ADD  CONSTRAINT [FK_Aborder_Article] FOREIGN KEY([idArticle])
REFERENCES [dbo].[Article] ([idArticle])
GO
ALTER TABLE [dbo].[Aborder] CHECK CONSTRAINT [FK_Aborder_Article]
GO
ALTER TABLE [dbo].[Aborder]  WITH CHECK ADD  CONSTRAINT [FK_Aborder_Thematique] FOREIGN KEY([idThematique])
REFERENCES [dbo].[Thematique] ([idThematique])
GO
ALTER TABLE [dbo].[Aborder] CHECK CONSTRAINT [FK_Aborder_Thematique]
GO
ALTER TABLE [dbo].[CategorieAge]  WITH CHECK ADD  CONSTRAINT [CK_CategorieAge_bornes] CHECK  (([ageMin]>=(0) AND [ageMin]<=[ageMax]))
GO
ALTER TABLE [dbo].[CategorieAge] CHECK CONSTRAINT [CK_CategorieAge_bornes]
GO
ALTER TABLE [dbo].[Produit]  WITH CHECK ADD  CONSTRAINT [CK_Produit_prixHt] CHECK  (([prixHt]>=(0)))
GO
ALTER TABLE [dbo].[Produit] CHECK CONSTRAINT [CK_Produit_prixHt]
GO
ALTER TABLE [dbo].[Produit]  WITH CHECK ADD  CONSTRAINT [CK_Produit_stock] CHECK  (([stock]>=(0)))
GO
ALTER TABLE [dbo].[Produit] CHECK CONSTRAINT [CK_Produit_stock]
GO
ALTER TABLE [dbo].[Produit]  WITH CHECK ADD  CONSTRAINT [CK_Produit_tauxTva] CHECK  (([tauxTva]>=(0)))
GO
ALTER TABLE [dbo].[Produit] CHECK CONSTRAINT [CK_Produit_tauxTva]
GO
ALTER TABLE [dbo].[Reduction]  WITH CHECK ADD  CONSTRAINT [CK_Reduction_taux] CHECK  (([txReduction]>=(0) AND [txReduction]<=(100)))
GO
ALTER TABLE [dbo].[Reduction] CHECK CONSTRAINT [CK_Reduction_taux]
GO
ALTER TABLE [dbo].[Tarif]  WITH CHECK ADD  CONSTRAINT [CK_Tarif_famille] CHECK  (([famille]='option' OR [famille]='formule'))
GO
ALTER TABLE [dbo].[Tarif] CHECK CONSTRAINT [CK_Tarif_famille]
GO
ALTER TABLE [dbo].[Tarif]  WITH CHECK ADD  CONSTRAINT [CK_Tarif_mode] CHECK  (([mode]='pourcentage' OR [mode]='fixe'))
GO
ALTER TABLE [dbo].[Tarif] CHECK CONSTRAINT [CK_Tarif_mode]
GO
ALTER TABLE [dbo].[Tarif]  WITH CHECK ADD  CONSTRAINT [CK_Tarif_valeur] CHECK  (([valeur]>=(0)))
GO
ALTER TABLE [dbo].[Tarif] CHECK CONSTRAINT [CK_Tarif_valeur]
GO
ALTER TABLE [dbo].[CompatibiliteProduit]  WITH CHECK ADD  CONSTRAINT [CK_Compatibilite_ordre] CHECK  (([idProduit1]<[idProduit2]))
GO
ALTER TABLE [dbo].[CompatibiliteProduit] CHECK CONSTRAINT [CK_Compatibilite_ordre]
GO
ALTER TABLE [dbo].[Image]  WITH CHECK ADD  CONSTRAINT [CK_Image_numOrdre] CHECK  (([numOrdre]>=(1)))
GO
ALTER TABLE [dbo].[Image] CHECK CONSTRAINT [CK_Image_numOrdre]
GO
ALTER TABLE [dbo].[Membre]  WITH CHECK ADD  CONSTRAINT [CK_Membre_niveau] CHECK  (([niveau]='confirmé' OR [niveau]='intermédiaire' OR [niveau]='débutant'))
GO
ALTER TABLE [dbo].[Membre] CHECK CONSTRAINT [CK_Membre_niveau]
GO
ALTER TABLE [dbo].[MessageContact]  WITH CHECK ADD  CONSTRAINT [CK_MessageContact_objet] CHECK  (([objet]='autre' OR [objet]='sav' OR [objet]='devis' OR [objet]='demo'))
GO
ALTER TABLE [dbo].[MessageContact] CHECK CONSTRAINT [CK_MessageContact_objet]
GO
ALTER TABLE [dbo].[Adhesion]  WITH CHECK ADD  CONSTRAINT [CK_Adhesion_annee] CHECK  (([annee]>=(2000) AND [annee]<=(2100)))
GO
ALTER TABLE [dbo].[Adhesion] CHECK CONSTRAINT [CK_Adhesion_annee]
GO
ALTER TABLE [dbo].[Adhesion]  WITH CHECK ADD  CONSTRAINT [CK_Adhesion_montant] CHECK  (([montant]>=(0)))
GO
ALTER TABLE [dbo].[Adhesion] CHECK CONSTRAINT [CK_Adhesion_montant]
GO
ALTER TABLE [dbo].[Animateur]  WITH CHECK ADD  CONSTRAINT [CK_Animateur_remplacant] CHECK  (([idRemplacant]<>[idUtilisateur]))
GO
ALTER TABLE [dbo].[Animateur] CHECK CONSTRAINT [CK_Animateur_remplacant]
GO
ALTER TABLE [dbo].[Commande]  WITH CHECK ADD  CONSTRAINT [CK_Commande_montantTotal] CHECK  (([montantTotal]>=(0)))
GO
ALTER TABLE [dbo].[Commande] CHECK CONSTRAINT [CK_Commande_montantTotal]
GO
ALTER TABLE [dbo].[Contenir]  WITH CHECK ADD  CONSTRAINT [CK_Contenir_prixUnitaireHt] CHECK  (([prixUnitaireHt]>=(0)))
GO
ALTER TABLE [dbo].[Contenir] CHECK CONSTRAINT [CK_Contenir_prixUnitaireHt]
GO
ALTER TABLE [dbo].[Contenir]  WITH CHECK ADD  CONSTRAINT [CK_Contenir_quantite] CHECK  (([quantite]>(0)))
GO
ALTER TABLE [dbo].[Contenir] CHECK CONSTRAINT [CK_Contenir_quantite]
GO
ALTER TABLE [dbo].[Evenement]  WITH CHECK ADD  CONSTRAINT [CK_Evenement_dates] CHECK  (([dateFin]>[dateDebut]))
GO
ALTER TABLE [dbo].[Evenement] CHECK CONSTRAINT [CK_Evenement_dates]
GO
ALTER TABLE [dbo].[Evenement]  WITH CHECK ADD  CONSTRAINT [CK_Evenement_nbPlaces] CHECK  (([nbPlaces]>=(0)))
GO
ALTER TABLE [dbo].[Evenement] CHECK CONSTRAINT [CK_Evenement_nbPlaces]
GO
ALTER TABLE [dbo].[Paiement]  WITH CHECK ADD  CONSTRAINT [CK_Paiement_montant] CHECK  (([montant]>=(0)))
GO
ALTER TABLE [dbo].[Paiement] CHECK CONSTRAINT [CK_Paiement_montant]
GO
ALTER TABLE [dbo].[Panier]  WITH CHECK ADD  CONSTRAINT [CK_Panier_nbPlace] CHECK  (([nbPlace]>(0)))
GO
ALTER TABLE [dbo].[Panier] CHECK CONSTRAINT [CK_Panier_nbPlace]
GO
ALTER TABLE [dbo].[PointOrdreJour]  WITH CHECK ADD  CONSTRAINT [CK_Point_ordre] CHECK  (([numOrdre]>=(1)))
GO
ALTER TABLE [dbo].[PointOrdreJour] CHECK CONSTRAINT [CK_Point_ordre]
GO
/****** Object:  Trigger [dbo].[trg_Utilisateur_Historisation]    Script Date: 06/10/2026 17:43:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE   TRIGGER trg_Utilisateur_Historisation ON Utilisateur
            AFTER UPDATE
            AS
            BEGIN
                -- Adhérent désactivé sans adhésion pour l'année en cours : historisation
                SET NOCOUNT ON;
                IF NOT UPDATE(actif) RETURN;
                INSERT INTO HistoriqueAdhesion (idUtilisateur, nom, prenom, derniereAnnee, motif)
                SELECT i.idUtilisateur, i.nom, i.prenom,
                       (SELECT MAX(a.annee) FROM Adhesion a WHERE a.idUtilisateur = i.idUtilisateur),
                       N'Adhésion non renouvelée'
                FROM inserted i
                JOIN deleted d ON d.idUtilisateur = i.idUtilisateur
                JOIN Client c  ON c.idUtilisateur = i.idUtilisateur
                WHERE d.actif = 1 AND i.actif = 0
                  AND NOT EXISTS (SELECT 1 FROM Adhesion a
                                  WHERE a.idUtilisateur = i.idUtilisateur AND a.annee = YEAR(GETDATE()));
            END
GO
ALTER TABLE [dbo].[Utilisateur] ENABLE TRIGGER [trg_Utilisateur_Historisation]
GO
/****** Object:  Trigger [dbo].[trg_Inscription_Mail]    Script Date: 06/10/2026 17:43:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE   TRIGGER trg_Inscription_Mail ON Inscription
            AFTER INSERT
            AS
            BEGIN
                -- Un mail de confirmation par inscription (gère les insertions multiples)
                SET NOCOUNT ON;
                INSERT INTO MailAEnvoyer (destinataire, objet, corps)
                SELECT u.email,
                       LEFT(N'Inscription confirmée : ' + e.titre, 150),
                       LEFT(N'Bonjour ' + u.prenom + N', votre inscription à « ' + e.titre + N' » le '
                            + CONVERT(VARCHAR(10), e.dateDebut, 103) + N' à ' + CONVERT(VARCHAR(5), e.dateDebut, 108)
                            + ISNULL(N' (' + s.nom + N')', N'') + N' est confirmée. À bientôt au Club Robotix !', 2000)
                FROM inserted i
                JOIN Utilisateur u  ON u.idUtilisateur = i.idMembre
                JOIN Evenement e    ON e.idEvenement = i.idEvenement
                LEFT JOIN Showroom s ON s.idShowroom = e.idShowroom;
            END
GO
ALTER TABLE [dbo].[Inscription] ENABLE TRIGGER [trg_Inscription_Mail]
GO
/****** Object:  Trigger [dbo].[trg_Inscription_NbEvenements]    Script Date: 06/10/2026 17:43:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE   TRIGGER trg_Inscription_NbEvenements ON Inscription
            AFTER INSERT, UPDATE, DELETE
            AS
            BEGIN
                -- Recalcule le nombre d'événements des membres concernés
                SET NOCOUNT ON;
                UPDATE m
                SET nbEvenements = (SELECT COUNT(*) FROM Inscription x WHERE x.idMembre = m.idUtilisateur)
                FROM Membre m
                WHERE m.idUtilisateur IN (SELECT idMembre FROM inserted UNION SELECT idMembre FROM deleted);
            END
GO
ALTER TABLE [dbo].[Inscription] ENABLE TRIGGER [trg_Inscription_NbEvenements]
GO

-- v_AdherentsRoles
CREATE   VIEW v_AdherentsRoles
            AS
            SELECT u.idUtilisateur, u.nom, u.prenom, u.email,
                   CAST(N'Entraîneur' AS NVARCHAR(20)) AS role, CAST(a.specialite AS NVARCHAR(80)) AS detail
            FROM Animateur a JOIN Utilisateur u ON u.idUtilisateur = a.idUtilisateur
            UNION ALL
            SELECT u.idUtilisateur, u.nom, u.prenom, u.email,
                   CAST(N'Joueur' AS NVARCHAR(20)), CAST(m.niveau AS NVARCHAR(80))
            FROM Membre m JOIN Utilisateur u ON u.idUtilisateur = m.idUtilisateur
GO

-- v_EvenementsPresents
CREATE   VIEW v_EvenementsPresents
            AS
            SELECT e.idEvenement, e.titre, e.dateDebut, e.type, u.idUtilisateur, u.nom, u.prenom, i.travailRealise
            FROM Inscription i
            JOIN Evenement e   ON e.idEvenement = i.idEvenement
            JOIN Utilisateur u ON u.idUtilisateur = i.idMembre
            WHERE i.present = 1
GO

-- ps_AdherentsRenouveles
CREATE   PROCEDURE ps_AdherentsRenouveles @annee INT
            AS
            BEGIN
                -- Adhérents ayant une adhésion pour @annee ET pour l'année précédente
                SET NOCOUNT ON;
                SELECT u.idUtilisateur, u.nom, u.prenom, u.email,
                       tp.libelle AS formulePrecedente, tn.libelle AS formuleAnnee
                FROM Adhesion n
                JOIN Adhesion p    ON p.idUtilisateur = n.idUtilisateur AND p.annee = @annee - 1
                JOIN Utilisateur u ON u.idUtilisateur = n.idUtilisateur
                JOIN Tarif tn      ON tn.idTarif = n.idTarif
                JOIN Tarif tp      ON tp.idTarif = p.idTarif
                WHERE n.annee = @annee
                ORDER BY u.nom, u.prenom;
            END
GO

-- ps_EvenementsSuivis
CREATE   PROCEDURE ps_EvenementsSuivis @idAdherent INT
            AS
            BEGIN
                -- Événements suivis par un adhérent : présence et travail réalisé
                SET NOCOUNT ON;
                SELECT u.nom, u.prenom, e.idEvenement, e.titre, e.dateDebut,
                       CASE i.present WHEN 1 THEN N'Présent' WHEN 0 THEN N'Absent' ELSE N'Non pointé' END AS presence,
                       i.travailRealise
                FROM Inscription i
                JOIN Evenement e   ON e.idEvenement = i.idEvenement
                JOIN Utilisateur u ON u.idUtilisateur = i.idMembre
                WHERE i.idMembre = @idAdherent
                ORDER BY e.dateDebut;
            END
GO

-- ps_HeuresEntrainement
CREATE   PROCEDURE ps_HeuresEntrainement
            AS
            BEGIN
                -- Heures d'atelier suivies (présent) par chaque joueur
                SET NOCOUNT ON;
                SELECT m.idUtilisateur, u.nom, u.prenom,
                       CAST(ISNULL(SUM(CASE WHEN i.present = 1 AND e.type = 'atelier'
                                            THEN DATEDIFF(MINUTE, e.dateDebut, e.dateFin) END), 0) / 60.0
                            AS DECIMAL(6,2)) AS heures
                FROM Membre m
                JOIN Utilisateur u     ON u.idUtilisateur = m.idUtilisateur
                LEFT JOIN Inscription i ON i.idMembre = m.idUtilisateur
                LEFT JOIN Evenement e   ON e.idEvenement = i.idEvenement
                GROUP BY m.idUtilisateur, u.nom, u.prenom
                ORDER BY heures DESC, u.nom;
            END
GO

-- ps_NbEvenementsEntreDates
CREATE   PROCEDURE ps_NbEvenementsEntreDates
                @idMembre INT, @debut DATE, @fin DATE, @nb INT OUTPUT
            AS
            BEGIN
                -- Nombre d'événements suivis (présent) par un joueur entre deux dates incluses
                SET NOCOUNT ON;
                SELECT @nb = COUNT(*)
                FROM Inscription i
                JOIN Evenement e ON e.idEvenement = i.idEvenement
                WHERE i.idMembre = @idMembre AND i.present = 1
                  AND CAST(e.dateDebut AS DATE) BETWEEN @debut AND @fin;
            END
GO

-- ps_OrdreDuJour
CREATE   PROCEDURE ps_OrdreDuJour @date DATE
            AS
            BEGIN
                -- Points à traiter des réunions d'une date, dans l'ordre
                SET NOCOUNT ON;
                SELECT r.idReunion, r.objet, CONVERT(VARCHAR(5), r.dateReunion, 108) AS heure,
                       p.numOrdre, p.libelle
                FROM Reunion r
                JOIN PointOrdreJour p ON p.idReunion = r.idReunion
                WHERE CAST(r.dateReunion AS DATE) = @date
                ORDER BY r.dateReunion, p.numOrdre;
            END
GO
