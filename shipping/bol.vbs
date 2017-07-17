Sub vbDoPrint()
       OLECMDID_PRINT = 6
       OLECMDEXECOPT_DONTPROMPTUSER = 2
       OLECMDEXECOPT_PROMPTUSER = 1
       call WB.ExecWB(OLECMDID_PRINT, OLECMDEXECOPT_DONTPROMPTUSER,1)
End Sub

Sub printLabel(name,street,citystzip)
	call PMDPrint.printLabel(name,street,citystzip)
End Sub

Sub printLabel_old(labelData)
	Dim Output
	Output = VbLf
	Output = Output & "N" & VbLf
	Dim addressName, addressStreet, addressStreet2, addressCityStZip, nameLen, streetLen, cityLen, longest, maxCharPixels, fontNum, mult
	addressName = labelData(0)
	addressStreet = labelData(1)
	
	' if the labelData array has > 3 elements, there are two street lines & adjust accordingly
	If UBound(labelData) = 2 Then
		addressCityStZip = labelData(2)
	Else
		addressStreet2 = labelData(2)
		addressCityStZip = labelData(3)
	End If
	
	' find the longest line, character count wise, and calc the font to use from that
	nameLen = Len(addressName)
	streetLen = Len(addressStreet)
	street2Len = Len(addressStreet2)
	If street2Len > streetLen Then streetLen = street2Len ' make streetLen the longer of the two street lines
	cityLen = Len(addressCityStZip)
	If nameLen >= streetLen Then
		If cityLen >= nameLen Then
			longest = cityLen
		Else
			longest = nameLen
		End If
	Else
		If cityLen >= streetLen Then
			longest = cityLen
		Else
			longest = streetLen
		End If
	End If
	maxCharPixels = 1180/longest
	Dim mods(5)
	mods(5) = maxCharPixels Mod 48
	mods(4) = maxCharPixels Mod 24
	mods(3) = maxCharPixels Mod 20
	mods(2) = maxCharPixels Mod 16
	mods(1) = maxCharPixels Mod 12
	Dim i
	For i = 5 to 1 Step -1
		If mods(i) < mods(i-1) Then fontNum = i
	Next
	' calculate how much we can multiply the font size by
	mult = Int(maxCharPixels / (8 + 4*fontNum))
	Dim secondstreet, secondoffset, lineX
	' does the second streetline exist?
	If street2Len > 0 Then
		secondstreet = True
		secondoffset = 50
	End If 
	Dim center, halfLine
	' center = coords of the center of the label (assuming 6" label size; change the 6 to whatever size label is being used)
	center = (203*6)/2
	' calculcate the starting location of the name line by subtracting from the center half of the line length
	halfLine = center - (nameLen * (8 + 4*fontNum))/2
	' A = output ASCII text; X,Y,rotation,fonttype,xmult,ymult,reverse,data
	Output = Output & "A700," & halfLine & ",1," & fontNum & "," & mult & "," & mult & ",N,""" & addressName & """" & VbLf
	If secondstreet Then
		lineX = 500 + secondoffset
	Else
		lineX = 500
	End If
	halfLine = center - (streetLen * (8 + 4*fontNum))/2
	Output = Output & "A" & lineX & "," & halfLine & ",1," & fontNum & "," & mult & "," & mult & ",N,""" & addressStreet & """" & VbLf
	If secondstreet Then
		lineX = 400
		halfLine = center - (street2Len * (8 + 4*fontNum))/2
		Output = Output & "A" & lineX & "," & halfLine & ",1," & fontNum & "," & mult & "," & mult & ",N,""" & addressStreet2 & """" & VbLf
	End If
	If secondstreet Then
		lineX = 250
	Else
		lineX = 300
	End If	
	halfLine = center - (cityLen * (8 + 4*fontNum))/2
	Output = Output & "A" & lineX & "," & halfLine & ",1," & fontNum & "," & mult & "," & mult & ",N,""" & addressCityStZip & """" & VbLf
	' Print one (1)
	Output = Output & "P1" & VbLf
	' MsgBox Output
	call PMDPrint.Write(Output)
End Sub