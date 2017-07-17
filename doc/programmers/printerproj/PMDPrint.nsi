# This example tests the compile time aspect of the Library macros
# more than the runtime aspect. It is more of a syntax example,
# rather than a usage example.

# Taken from Library example and tweaked to be the right one

!include "Library.nsh"

Name "PMDPrint"
OutFile "PMDPrint.exe"
Icon Resources\crown.ico


Page instfiles

XPStyle on

!define PrintDLL '"ZebraPrinter\ZebraPrinter\bin\Release\PMDPrint.dll"'
!define WalmartComLogo '"ZebraPrinter\ZebraPrinter\bin\Release\walmart_com.jpg"'
!define WalmartLogo '"ZebraPrinter\ZebraPrinter\bin\Release\wm-logo-bw.tif"'
;!define ZoneSet '"SetZoneMappingDemo\SetZoneMappingDemo\bin\Release\PMD Internet Zone Changer.exe"'

# this function copied / modified from http://nsis.sourceforge.net/Get_directory_of_installed_.NET_runtime

; Given a .NET version number, function returns the .NET Framework's install dir
; Returns "" if the given .NET version is not installed.
; Parameters: {version} (e.g. "v2.0")
; Returns: {dir} (e.g. "C:\WINNT\Microsoft.NET\Framework\v2.0.50727")

Function GetDotNetDir
	Exch $R0 ; set R0 to .NET version, major
	Push $R1
	Push $R2

	; set R1 to minor version number of the installed .NET runtime
	EnumRegValue $R1 HKLM "Software\Microsoft\.NetFramework\policy\$R0" 0
	IfErrors getdotnetdir_err

	; set R2 to .NET install dir root
	ReadRegStr $R2 HKLM "Software\Microsoft\.NetFramework" "InstallRoot"
	IfErrors getdotnetdir_err

	; set R0 to the .NET install dir full
	StrCpy $R0 "$R2$R0.$R1"

getdotnetdir_end:
	Pop $R2
	Pop $R1
	Exch $R0 ; Return .net install dir full
	Return

getdotnetdir_err:
	StrCpy $R0 ""
	Goto getdotnetdir_end

FunctionEnd



Section "Install"
MessageBox MB_YESNO|MB_ICONINFORMATION "In order to use this plugin in Vista, we have to turn off User Account Control. Do you wish to continue?" IDNO +2
  Call DoInstall ; Calls our actual install code
SectionEnd

Function DoInstall
# get directory of .NET Framework install
Push "v2.0"
Call GetDotNetDir ; calls function above to get the .NET install dir
Pop $R0
StrCmpS "" $R0 err_dot_net_not_found ; if the .NET isn't found, abort

; do the install
SetOutPath $PROGRAMFILES\PMDPrint
MessageBox MB_OK "Please make sure all Internet Explorer Windows are closed before continuing."
File ${PrintDLL}
File ${WalmartComLogo}
File ${WalmartLogo}
; Create Storage for Our Printer
WriteRegStr HKCU "SOFTWARE\PMD\PMDPrint" "LabelPrinter" ""
; Turn on Web Browser Scripting on Trusted Domains
WriteRegDWORD HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\Zones\2" "1201" 0x0
; Turn on using ActiveX on Trusted Domains
WriteRegDWORD HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\Zones\2" "1206" 0x0
; Turn off the requirement for HTTPS when dealing with trusted sites.
WriteRegDWORD HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\Zones\2" "Flags" 0x43
; For Vista Turn off UAC
WriteRegDWORD HKLM "Software\Microsoft\Windows\CurrentVersion\Policies\System" "EnableLUA" 0x0
; Add ostin/www.pmddealer.com to the Trusted Sites
WriteRegDWORD HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\ZoneMap\Domains\pmddealer.com\ostin" "http" 0x2
WriteRegDWORD HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\ZoneMap\Domains\pmddealer.com\ostin" "https" 0x2
WriteRegDWORD HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\ZoneMap\Domains\pmddealer.com\www" "http" 0x2
WriteRegDWORD HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\ZoneMap\Domains\pmddealer.com\www" "https" 0x2
; Register Plugin DLL
nsExec::ExecToLog '"$R0\RegAsm.exe" "$PROGRAMFILES\PMDPrint\PMDPrint.dll" /tlb /codebase'
; Clean out old *.pmddealer.com
DeleteRegKey HKCU "SOFTWARE\Microsoft\Windows\CurrentVersion\Internet Settings\ZoneMap\Domains\pmddealer.com\*"
MessageBox MB_YESNO|MB_ICONQUESTION "To complete the installation, you need to reboot. Do this now?" IDNO +2
  Reboot
Return

err_dot_net_not_found:
	Abort ".NET Framework 2.0 Required"

FunctionEnd


; The following are ideas that diddn't work but we want to keep around just in case

; Try to turn off Protected Mode in Vista
;WriteRegDWORD HKLM "SOFTWARE\Microsoft\Active Setup\Installed Components\{A509B1A7-37EF-4b3f-8CFC-4F3A74704073}" "IsInstalled" 0x0
;WriteRegDWORD HKLM "SOFTWARE\Microsoft\Active Setup\Installed Components\{A509B1A8-37EF-4b3f-8CFC-4F3A74704073}" "IsInstalled" 0x0
;nsExec::ExecToLog 'Rundll32 iesetup.dll, IEHardenLMSettings'
;nsExec::ExecToLog 'Rundll32 iesetup.dll, IEHardenUser'
;nsExec::ExecToLog 'Rundll32 iesetup.dll, IEHardenAdmin'
;DeleteRegKey HKCU "SOFTWARE\Microsoft\Active Setup\Installed Components\{A509B1A7-37EF-4b3f-8CFC-4F3A74704073}"
;DeleteRegKey HKCU "SOFTWARE\Microsoft\Active Setup\Installed Components\{A509B1A8-37EF-4b3f-8CFC-4F3A74704073}"
;DeleteRegValue HKCU "Software\Microsoft\Internet Explorer" "Main"

; Add the PMDPrint.PMDPrint GUID to Low Rights Elevation group, hopefully as an IE in Vista Fix
;WriteRegStr HKLM "SOFTWARE\Microsoft\Internet Explorer\Low Rights\ElevationPolicy\{9CF0975F-43DB-3307-83FF-8E73172C723C}" "AppName" "PMDPrint.dll"
;WriteRegStr HKLM "SOFTWARE\Microsoft\Internet Explorer\Low Rights\ElevationPolicy\{9CF0975F-43DB-3307-83FF-8E73172C723C}" "AppPath" "$PROGRAMFILES\PMDPrint\PMDPrint.dll"
;WriteRegDWORD HKLM "SOFTWARE\Microsoft\Internet Explorer\Low Rights\ElevationPolicy\{9CF0975F-43DB-3307-83FF-8E73172C723C}" "Policy" 0x3